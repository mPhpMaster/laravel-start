<?php
/*
 * Copyright © 2021. mPhpMaster(https://github.com/mPhpMaster) All rights reserved.
 */

// region: Getters
if ( !function_exists('getModelAbstractClass') ) {
    /**
     * @param object|string|null $test_class
     *
     * @return string|bool
     * @todo: class \Model
     */
    function getModelAbstractClass($test_class = null)
    {
        if ( $test_class ) {
            $test_class = is_object($test_class) ? $test_class : app(getRealClassName($test_class));

            $test_abstract_class = getModelAbstractClass();
            return $test_class instanceof $test_abstract_class;
        }

        return \Model::class;
    }
}

if ( !function_exists('getRealClassName') ) {
    /**
     * Returns the real class name.
     *
     * @param string|object $class <p> The tested class. This parameter may be omitted when inside a class. </p>
     *
     * @return string|false <p> The name of the class of which <i>`class`</i> is an instance.</p>
     * <p>
     *      Returns <i>`false`</i> if <i>`class`</i> is not an <i>`class`</i>.
     *      If <i>`class`</i> is omitted when inside a class, the name of that class is returned.
     * </p>
     */
    function getRealClassName($class)
    {
        if ( is_object($class) ) {
            $class = get_class($class);
        }
        throw_if(!class_exists($class), new Exception("Class `{$class}` not exists!"));

        try {
            $_class = eval("return new class extends {$class} { };");

        } catch (Exception $exception) {
            throw $exception;
        }

        if ( $_class && is_object($_class) ) {
            return get_parent_class($_class);
        }

        return false;
    }
}

if ( !function_exists('basenameOf') ) {
    /**
     * Returns basename of the given string after replace slashes and back slashes to DIRECTORY_SEPARATOR
     *
     * @param string $string
     *
     * @return string
     */
    function basenameOf(string $string)
    {
        $string = (string)str_ireplace('/', DIRECTORY_SEPARATOR,
            str_ireplace('\\', DIRECTORY_SEPARATOR, $string)
        );

        return basename($string);
    }
}

if ( !function_exists('getNewValidator') ) {
    /**
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     *
     * @return \Illuminate\Validation\Validator
     */
    function getNewValidator($data, $rules, $messages = [], $customAttributes = [])
    {
        return \Illuminate\Support\Facades\Validator::make($data, $rules, $messages = [], $customAttributes = []);
    }
}

if ( !function_exists('getTrans') ) {
    /**
     * Returns Translation or return default.
     *
     * @param string|null $lang_path lang path
     * @param null|mixed  $default   default value to return if trans not exists
     *
     * @return mixed
     */
    function getTrans($lang_path, $default = null)
    {
        $trans = ($trans = __($lang_path)) != $lang_path ? $trans : $default;

        return $trans;
    }
}

if ( !function_exists('stringStarts') ) {
    /**
     * Determine if a given string starts with a given substring.
     *
     * @param string          $haystack
     * @param string|string[] $needles
     *
     * @return bool
     */
    function stringStarts($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ( (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0 ) {
                return true;
            }
        }

        return false;
    }
}

if ( !function_exists('real_path') ) {
    /**
     * return given path without ../
     *
     * @param null   $path
     * @param string $DIRECTORY_SEPARATOR
     *
     * @return string
     */
    function real_path($path = null, $DIRECTORY_SEPARATOR = "/")
    {
        $_DIRECTORY_SEPARATOR = $DIRECTORY_SEPARATOR === "/" ? "\\" : "/";
        if ( $path ) $path = str_ireplace($_DIRECTORY_SEPARATOR, $DIRECTORY_SEPARATOR, $path);

        $a = 0;
        if ( stringStarts($path, ['./']) ) {
            $path = substr($path, 2);
            $path = base_path($path);
            $a = 1;
        }

        $backslash = "..{$DIRECTORY_SEPARATOR}";
        if ( stripos($path, $backslash) !== false ) {
            $path = collect(explode($backslash, $path))->reverse();
            $path = $path->map(function ($v, $i) use ($path) {
                $_v = ($_v = dirname($v)) === '.' ? '' : $_v;
                return $i == $path->count() - 1 ? $v : $_v;
            });
            $path = str_ireplace(
                $DIRECTORY_SEPARATOR . $DIRECTORY_SEPARATOR,
                $DIRECTORY_SEPARATOR,
                $path->reverse()->implode($DIRECTORY_SEPARATOR)
            );
        }

        $path = str_ireplace(
            './',
            '/',
            fixPath($path)
        );

        return collect($path)->first();
    }
}

/**
 * return int
 */
if ( !function_exists('countToken') ) {
    /**
     * Return count of the given token.
     *
     * @param string $token
     * @param string $subject
     *
     * @return int
     */
    function countToken($token, $subject): int
    {
        if ( empty($_token = trim($token)) || empty($_subject = trim($subject)) ) {
            return 0;
        }

        return count(explode($token, $subject)) - 1;
    }
}
// endregion: Getters

// region: Is
/**
 * return bool
 */
if ( !function_exists('isClosure') ) {
    /**
     * Check if the given var is Closure.
     *
     * @param mixed|null $closure
     *
     * @return bool
     */
    function isClosure($closure): bool
    {
        return $closure instanceof Closure;
    }
}

/**
 * return bool
 */
if ( !function_exists('isCallableDeep') ) {
    /**
     * Check if the given var is Closure or|and callable or|and string or|and Array or|and object.
     *
     * @param mixed $closure
     * @param bool  $mustBeCallable
     * @param bool  $mustBeClosure
     * @param bool  $mustBeString
     * @param bool  $mustBeArray
     * @param bool  $mustBeObject
     *
     * @return bool
     */
    function isCallableDeep($closure,
                            bool $mustBeCallable = false,
                            bool $mustBeClosure = false,
                            bool $mustBeString = false,
                            bool $mustBeArray = false,
                            bool $mustBeObject = false): bool
    {
        $_result = [
            'callable' => is_callable($closure),
            'closure' => isClosure($closure),
            'string' => is_string($closure),
            'array' => is_array($closure),
            'object' => is_object($closure),
        ];

        if (
            ($mustBeCallable === true && $_result['callable'] === false) ||
            ($mustBeClosure === true && $_result['closure'] === false) ||
            ($mustBeString === true && $_result['string'] === false) ||
            ($mustBeArray === true && $_result['array'] === false) ||
            ($mustBeObject === true && $_result['object'] === false)
        ) {
            return false;
        }
        if (
            ($mustBeCallable === false && $_result['callable'] === true) ||
            ($mustBeClosure === false && $_result['closure'] === true) ||
            ($mustBeString === false && $_result['string'] === true) ||
            ($mustBeArray === false && $_result['array'] === true) ||
            ($mustBeObject === false && $_result['object'] === true)
        ) {
            return true;
        }

        return count(array_filter($_result, fn($v) => $v === true)) > 0;
//        $result = $mustBeCallable ? is_callable($closure) : $result;
//        $result = $mustBeClosure ? isClosure($closure) : $result;
//        $result = $mustBeString ? is_string($closure) : $result;
//        $result = $mustBeArray ? is_array($closure) : $result;
//        $result = $mustBeObject ? is_object($closure) : $result;
//
//        return $result;
    }
}

/**
 * return bool
 */
if ( !function_exists('isArrayableOrArray') ) {
    /**
     * Check if the given var is Array | is Arrayable (has ->toArray()).
     *
     * @param mixed|null $array
     *
     * @return bool
     */
    function isArrayableOrArray($array): bool
    {
        return is_array($array) || isArrayable($array);
    }
}

/**
 * return bool
 */
if ( !function_exists('isArrayable') ) {
    /**
     * Check if the given var is Arrayable (has ->toArray()).
     *
     * @param mixed|null $array
     *
     * @return bool
     */
    function isArrayable($array): bool
    {
        return
            (interface_exists(\Illuminate\Contracts\Support\Arrayable::class) && $array instanceof \Illuminate\Contracts\Support\Arrayable) ||
            (interface_exists(\Illuminate\Contracts\Support\Arrayable::class) && $array instanceof \Arrayable) ||
            method_exists($array, 'toArray');
    }
}
// endregion: Is

// region: to
if ( !function_exists('toCollect') ) {
    /**
     * Returns $var as collection
     *
     * @param $var
     *
     * @return \Illuminate\Support\Collection
     */
    function toCollect($var): \Illuminate\Support\Collection
    {
        return is_collection($var) ? $var : collect($var);
    }
}

if ( !function_exists('toCollectWithModel') ) {
    /**
     * Returns $var as collection, if the given var is model ? return collect([model])
     *
     * @param $var
     *
     * @return \Illuminate\Support\Collection
     */
    function toCollectWithModel($var): \Illuminate\Support\Collection
    {
        $var = $var instanceof \Illuminate\Database\Eloquent\Model ? [$var] : $var;
        return toCollect($var);
    }
}

if ( !function_exists('toCollectOrModel') ) {
    /**
     * Returns $var as collection, if the given var is model ? return model
     *
     * @param $var
     *
     * @return \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model
     */
    function toCollectOrModel($var)
    {
        return is_collection($var) || $var instanceof \Illuminate\Database\Eloquent\Model ? $var : collect($var);
    }
}

if ( !function_exists('trimLower') ) {
    /**
     * @param string $string
     *
     * @return string
     */
    function trimLower(?string $string)
    {
        return strtolower(trim($string));
    }
}
// endregion: to

// region: Others
if ( !function_exists('createNewValidator') ) {
    /**
     * Create New Validator.
     *
     * @param string        $name
     * @param \Closure|null $closure
     */
    function createNewValidator($name, Closure $closure = null)
    {
        \Illuminate\Support\Facades\Validator::extend(trim($name), $closure && $closure instanceof Closure ? $closure : fn($attribute, $value, $parameters) => $value);
    }
}

/**
 * return mixed
 */
if ( !function_exists('replaceAll') ) {
    /**
     * Replace a given data in string.
     *
     * @param \Illuminate\Contracts\Support\Arrayable<mixed, \Closure|callable|mixed>|array<mixed, \Closure|callable|mixed> $_searchAndReplace
     * @param string                                                                                                        $_subject
     *
     * @return string
     */
    function replaceAll($_searchAndReplace, $_subject)
    {
        if ( isArrayableOrArray($_subject) && !isArrayableOrArray($_searchAndReplace) ) {
            $searchAndReplace = $_subject;
            $subject = $_searchAndReplace;
        } else {
            $searchAndReplace = $_searchAndReplace;
            $subject = $_subject;
        }

        toCollect((array)$searchAndReplace)->each(function ($replace, $search) use (&$subject) {
            if (
                !is_string($replace) &&
                isCallableDeep(
                    $replace,
                    false,
                    false,
                    false,
                    false,
                    false
                )
            ) {
                $_args = [
                    'search' => $search,
                    'subject' => $subject,
                    'count' => trim($count = countToken($search, $subject)),
                ];

                $_replace = call_user_func($replace, $_args);
            } else {
                $_replace = $replace;
            }

            $subject = str_ireplace($search, $_replace, $subject);
        });

        return $subject;
    }
}

if ( !function_exists('routeModel') ) {
    /**
     * Register a model binder for a wildcard.
     *
     * @param  string  $key
     * @param  string  $class
     * @param  \Closure|null  $callback
     *                                 
     * @link \Illuminate\Support\Facades\Route::model
     *                                 
     * @return void
     */
    function routeModel($key, $class, Closure $callback = null)
    {
        \Illuminate\Support\Facades\Route::model($key, $class, $callback);
    }
}
// endregion: Others