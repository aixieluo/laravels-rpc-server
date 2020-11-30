<?php

if (! function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param string|null $abstract
     * @param array       $parameters
     *
     * @return mixed|\Illuminate\Contracts\Foundation\Application
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    function app($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return \Illuminate\Container\Container::getInstance();
        }

        return \Illuminate\Container\Container::getInstance()->make($abstract, $parameters);
    }
}

if (! function_exists('req')) {
    /**
     * Get an instance of the current request or an input item from the request.
     *
     * @return \Illuminate\Http\Request|string|array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    function req()
    {
        app()->singletonIf('request', function () {
            return \Illuminate\Http\Request::capture();
        });
        return  app('request');
    }
}
