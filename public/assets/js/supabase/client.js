// file path = public/assets/js/supabase/client.js

/**
 * Subscribe to Supabase using anon key only
 * @param {string} url - Supabase URL
 * @param {string} key - Supabase anon key
 */
export function subscribeToSupabase(url, key) {
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

    return supabaseClient;
}