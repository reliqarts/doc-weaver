<?php

namespace ReliQArts\DocWeaver\Traits;

use Config;

trait RouteHelper
{
    /**
     * Get route config.
     *
     * @return array Config.
     */
    public static function getRouteConfig()
    {
        return Config::get('doc-weaver.route', []);
    }

    /**
     * Get route prefix for docs.
     *
     * @return string Prefix.
     */
    public static function getRoutePrefix()
    {
        return Config::get('doc-weaver.route.prefix', 'docs');
    }

    /**
     * Get bindings for routes.
     */
    public static function getRouteGroupBindings($bindings = [])
    {
        $defaults = ['prefix' => self::getRoutePrefix()];
        $bindings = array_merge(Config::get('doc-weaver.route.bindings', []), $bindings);

        return array_merge($defaults, $bindings);
    }
}
