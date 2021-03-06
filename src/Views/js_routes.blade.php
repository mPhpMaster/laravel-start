
//<script>
    const route = function(routeName, args) {
        let argsKeys = [];

        // route exists
        if(routeName && route.routes && routeName in route.routes) {
            let $route = route.routes[routeName],
                $needArgs = [];

            // read route vars
            if(route.routeParam && routeName in route.routeParam) {
                $needArgs = route.routeParam[routeName];
            } else {
                $route.replace(/\{(?<var>[\w\b]+)\}/gi, function($full, $var) {
                    $needArgs.push([$var, $full]);
                    return $full;
                });
                route.routeParam[routeName] = Array.from($needArgs);
            }

            // read received arguments
            let hasArgs = args && typeof args === 'object' && (argsKeys = Object.keys(args)).length > 0;

            // route need args & no arguments received .. error
            if(!hasArgs && $needArgs.length) {
                throw new ReferenceError(`Missing required parameters for [Route: ${routeName}] [URI: ${$route}]`);
            }
            // route need args && arguments received .. parse
            else if(hasArgs && $needArgs.length) {
                if(!Array.isArray(args)) {
                    argsKeys = argsKeys.filter(x => !(route.containArgs($route) && ($route = $route.replace(`{${x}}`, args[x]))));

                } else {
                    argsKeys = argsKeys.filter((i, x) => !($route += `${args[x]}&`));
                }

                // parse remaining vars as queryString
                if(route.containArgs($route) || argsKeys.length > 0) {
                    $route = argsKeys.length ? route.parseArgsAsQuery($route, argsKeys, args) : $route;
                } else if(route.containArgs($route) && !argsKeys.length){
                    throw new ReferenceError(`Missing required parameters for [Route: ${routeName}] [URI: ${$route}]`);
                }
            }
            // route dose not need args && arguments received .. parse
            else if(hasArgs && !$needArgs.length) {
                $route += $route.slice(-1) === '?' ? '' : '?';
                if(!Array.isArray(args)) {
                    argsKeys = argsKeys.filter(x=>!($route += `${x}=${args[x]}&`));
                }
                else {
                    argsKeys = argsKeys.filter((i, x)=>!($route += `${args[x]}&`));
                }
                $route = $route.slice(-1) === '&' ? $route.slice(0, -1) : $route;
            }
            // route dose not need args && no arguments received .. return
            else if(!hasArgs && !$needArgs.length) {}

            // check if any argument is not assigned
            if(route.containArgs($route)) {
                throw new ReferenceError(`Missing required parameters for [Route: ${routeName}] [URI: ${$route}].`);
            }

            $route = $route && $route.charAt(0) !== '/' ? `/${$route}` : $route;
            return $route = `${route.fullAppLink}${$route}`;
        }
        // route not found
        else {
            throw new ReferenceError(`Route [${routeName}] not defined.`);
        }
    };
    route.routes = {};

    route.containArgs = x => /\{(?<var>[\w\b]+)\}/gi.test(x);
    route.parseArgsAsQuery = function parseArgsAsQuery($route, remainKeys, args) {
        let $_key;
        $route += $route.slice(-1) === '?' ? '' : '?';
        while(($_key = remainKeys.shift()) || remainKeys.length) {
            if(!Array.isArray(args)) {
                $route += `${$_key}=${args[$_key]}&`;
            } else {
                $route += `${args[$_key]}&`;
            }
        }

        return $route = $route.slice(-1) === '&' ? $route.slice(0, -1) : $route;
    };

    try {
        route.routeParam = {};
        route.fullAppLink = `{{url('/')}}`;
        route.routes = @json($content/*, JSON_PRETTY_PRINT*/);
    } catch (e) {
        Dir("No routes!");
    }

    // console.log(route.routes);

//</script>