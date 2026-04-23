// file path = public/assets/js/supabase/client.js

/**
 * Subscribe to Supabase using anon key only
 * @param {string} url - Supabase URL
 * @param {string} key - Supabase anon key
 * @param {string|null} accessToken - Supabase access token for authenticated Realtime
 */
export function subscribeToSupabase(url, key, accessToken = null) {
    const supabaseUrl = url;
    const supabaseKey = key;

    const supabaseClient = supabase.createClient(
        supabaseUrl,
        supabaseKey,
        {
            auth: {
                persistSession: false,
                autoRefreshToken: false,
            }
        }
    );

    if (accessToken) {
        supabaseClient.realtime.setAuth(accessToken);
    }

    return supabaseClient;
}
