<?php

if (! function_exists('req')) {
    /**
     * Get an instance of the current request or an input item from the request.
     *
     * @return \Illuminate\Http\Request|string|array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    function req()
    {
        \Illuminate\Container\Container::getInstance()->singletonIf('request', function () {
            return \Illuminate\Http\Request::capture();
        });
        return  \Illuminate\Container\Container::getInstance()->make('request');
    }
}
