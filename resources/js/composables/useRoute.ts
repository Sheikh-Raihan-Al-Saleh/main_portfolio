import { route as ziggyRoute } from 'ziggy-js';
import type { RouteFormAttributes, RouteParameters } from '@/types';

/**
 * URLs of named Laravel routes, as in Blade: route('projectEdit', project.slug).
 *
 * The route list comes from the @routes Blade directive (Ziggy). URLs are
 * relative ("/admin/projects"), so they work on any host and match the
 * current page URL when highlighting active links.
 */
export function route(name: string, parameters?: RouteParameters): string {
    // Ziggy types a single value and an object/list of values separately.
    if (typeof parameters === 'string' || typeof parameters === 'number') {
        return ziggyRoute(name, parameters, false);
    }

    return ziggyRoute(name, parameters, false);
}

/**
 * The `action` and `method` of an Inertia <Form> that submits to a named
 * route: <Form v-bind="routeForm('projectUpdate', project.slug)">.
 *
 * HTML forms only send GET and POST, and PHP cannot read uploaded files from
 * a PUT, PATCH or DELETE request, so those are sent as POST with a `_method`
 * field in the query string, which Laravel turns back into the real method.
 */
export function routeForm(
    name: string,
    parameters?: RouteParameters,
): RouteFormAttributes {
    const action = route(name, parameters);
    const method = routeMethod(name);

    if (method === 'get' || method === 'post') {
        return { action, method };
    }

    const separator = action.includes('?') ? '&' : '?';

    return {
        action: `${action}${separator}_method=${method.toUpperCase()}`,
        method: 'post',
    };
}

/**
 * The HTTP method a named route answers to, lower case ("get", "put"...).
 *
 * @routes declares the list as a global `const Ziggy`, which is a global
 * binding but not a property of `window`, so it is read by name.
 */
export function routeMethod(name: string): string {
    const routeList = typeof Ziggy === 'undefined' ? undefined : Ziggy;
    const methods = routeList?.routes[name]?.methods;

    if (!methods) {
        throw new Error(`Route [${name}] is not in the route list.`);
    }

    const method = methods.find((candidate) => candidate !== 'HEAD') ?? 'GET';

    return method.toLowerCase();
}
