<?php

/**
 * Check if the authenticated user can access beta features
 * 
 * @return bool True if user ID is in beta_users_ids, false otherwise
 */
function can_assess_beta_features() {
    // Get the authenticated user ID from session or auth
    $auth_id = auth()->id(); // Adjust based on your auth implementation
    
    // If no authenticated user, return false
    if (!$auth_id) {
        return false;
    }
    
    // Get beta users IDs from config
    $beta_users_ids = config('app.beta_users_ids', []);
    
    // Check if current auth ID is in beta users list
    return in_array($auth_id, $beta_users_ids, true);
}
