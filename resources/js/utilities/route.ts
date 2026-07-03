type RouteParams = Record<string, unknown> | string | number | unknown[] | undefined;

/**
 * Resolve a named route to a URL without ever throwing.
 *
 * Ziggy's `route()` throws when a name is missing from its route list. During
 * development the in-page Ziggy list can be stale (routes are only injected on a
 * full page load, not on Inertia visits), and a single throw inside a render or
 * computed crashes the whole Vue reactivity tree - freezing every toggle,
 * dropdown and backdrop until a hard reload. This wrapper returns the fallback
 * instead so the UI degrades gracefully.
 */
export function safeRoute(
    name: string,
    params?: RouteParams,
    absolute?: boolean,
    fallback = '#',
): string {
    try {
        if (typeof route !== 'function' || !route().has(name)) {
            return fallback;
        }

        return route(name, params as never, absolute) as string;
    } catch {
        return fallback;
    }
}

/**
 * Check whether the current URL matches a named route without ever throwing.
 *
 * Mirrors {@link safeRoute}: Ziggy's `route().current(name)` throws when the name
 * is absent from a stale in-page route list, which crashes the surrounding render
 * or computed. Returns `false` instead so the UI degrades gracefully.
 */
export function safeCurrent(name: string): boolean {
    try {
        if (typeof route !== 'function' || !route().has(name)) {
            return false;
        }

        return Boolean(route().current(name));
    } catch {
        return false;
    }
}
