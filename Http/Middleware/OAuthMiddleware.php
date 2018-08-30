<?php
/*
 * This file is part of the congraph/oauth-2 package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\OAuth2\Http\Middleware;

use Closure;
use League\OAuth2\Server\Exception\InvalidScopeException;
use League\OAuth2\Server\Exception\OAuthException;
use LucaDegasperi\OAuth2Server\Authorizer;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use stdClass;

/**
 * oauth middleware class
 *
 * Middleware for handling oauth scope privilegies
 *
 * @uses   		LucaDegasperi\OAuth2Server\Authorizer
 *
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/oauth-2
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class OAuthMiddleware
{
    /**
     * The Authorizer instance.
     *
     * @var \LucaDegasperi\OAuth2Server\Authorizer
     */
    protected $authorizer;

    /**
     * Whether or not to check the http headers only for an access token.
     *
     * @var bool
     */
    protected $httpHeadersOnly = false;



    // something to keep track of parens nesting
    protected $stack = null;
    // current level
    protected $current = null;

    // input string to parse
    protected $string = null;
    // current character offset in string
    protected $position = null;
    // start of text-buffer
    protected $buffer_start = null;

    /**
     * Create a new oauth middleware instance.
     *
     * @param \LucaDegasperi\OAuth2Server\Authorizer $authorizer
     * @param bool $httpHeadersOnly
     */
    public function __construct(Authorizer $authorizer, $httpHeadersOnly = false)
    {
        $this->authorizer = $authorizer;
        $this->httpHeadersOnly = $httpHeadersOnly;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $scopesString
     *
     * @throws \League\OAuth2\Server\Exception\InvalidScopeException
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $scopesString = null)
    {
        $scopes = [];

        try
        {
          $scopes = $this->parseScopes($scopesString);

          $this->authorizer->setRequest($request);

          $this->authorizer->validateAccessToken($this->httpHeadersOnly);
          $this->validateScopes($scopes, $scopesString);

          return $next($request);
        }
        catch (OAuthException $exception)
        {
            throw new UnauthorizedHttpException('Bearer', $exception->getMessage(), $exception);
        }


    }

    /**
     * Validate the scopes.
     *
     * @param $scopes
     *
     * @throws \League\OAuth2\Server\Exception\InvalidScopeException
     */
    public function validateScopes(array $scopes, $scopesString = '')
    {
        $valid = true;
        $operator = null;
        foreach ($scopes as $value)
        {
            if(is_array($value))
            {
              $result = $this->validateScopes($value);
              $valid = $this->check($valid, $result, $operator);
              continue;
            }

            if($value == '&' || $value == '|')
            {
              $operator = $value;
              continue;
            }

            $result = $this->authorizer->hasScope($value);

            $valid = $this->check($valid, $result, $operator);
        }
        if (!$valid)
        {
            throw new InvalidScopeException($scopesString);
        }
    }

    protected function check($first, $second, $operator = null)
    {
      if($operator === '|')
      {
        return $first || $second;
      }

      return $first && $second;
    }

    /**
     * Parse scopes string to array.
     *
     * @param $scopesString
     *
     * @throws \League\OAuth2\Server\Exception\InvalidScopeException
     */
    public function parseScopes($scopesString)
    {
        if (!$scopesString)
        {
            // no string, no data
            return array();
        }

        if(strpos($scopesString, ' ') !== false)
        {
          // spaces are not allowed in scopes string
          throw new InvalidScopeException($scopesString);
        }

        if ($scopesString[0] == '(' && $scopesString[strlen($scopesString) - 1] == ')')
        {
            // killer outer parens, as they're unnecessary
            $scopesString = substr($scopesString, 1, -1);
        }

        $this->current = array();
        $this->stack = array();

        $this->string = $scopesString;
        $this->length = strlen($this->string);

        $level = 0;

        // look at each character
        for ($this->position=0; $this->position < $this->length; $this->position++) {
            switch ($this->string[$this->position]) {
                case '(':
                    $this->push();
                    $level++;
                    if(count($this->stack))
                    {
                        $lastItemInStack = $this->stack[count($this->stack) - 1];
                        if($lastItemInStack !== '&' && $lastItemInStack !== '|')
                        {
                            // there is a mistake with & and | operators
                            throw new InvalidScopeException($scopesString);
                        }
                    }

                    // push current scope to the stack an begin a new scope
                    array_push($this->stack, $this->current);
                    $this->current = array();
                    break;

                case ')':
                    $this->push();
                    $level--;
                    if(count($this->stack))
                    {
                        $lastItemInStack = $this->stack[count($this->stack) - 1];
                        if($lastItemInStack == '&' || $lastItemInStack == '|')
                        {
                            // there is a mistake with & and | operators
                            throw new InvalidScopeException($scopesString);
                        }
                    }
                    // save current scope
                    $t = $this->current;
                    // get the last scope from stack
                    $this->current = array_pop($this->stack);
                    // add just saved scope to current scope
                    $this->current[] = $t;
                    break;
                case '&':
                case '|':
                    $this->push($this->string[$this->position]);
                    break;
                default:
                    // remember the offset to do a string capture later
                    // could've also done $buffer .= $string[$position]
                    // but that would just be wasting resources…
                    if ($this->buffer_start === null) {
                        $this->buffer_start = $this->position;
                    }
            }
        }

        if($level !== 0)
        {
          // there is a mistake in brackets
          throw new InvalidScopeException($scopesString);
        }

        if($this->buffer_start !== null)
        {
          $this->push();
        }
        if(count($this->current))
        {
            $lastItem = $this->current[count($this->current) - 1];
            if($lastItem == '&' || $lastItem == '|')
            {
              // there is a mistake with & and | operators
              throw new InvalidScopeException($scopesString);
            }
        }

        return $this->current;
    }

    protected function push($operator = null)
    {
        if ($this->buffer_start !== null) {
            // extract string from buffer start to current position
            $buffer = substr($this->string, $this->buffer_start, $this->position - $this->buffer_start);
            // clean buffer
            $this->buffer_start = null;
            // throw token into current scope
            $this->current[] = $buffer;

            if($operator)
            {
              $this->current[] = $operator;
            }
        }
    }
}
