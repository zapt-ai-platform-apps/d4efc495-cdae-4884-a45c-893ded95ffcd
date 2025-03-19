<?php
/**
 * Class for handling geocoding
 */
class FVL_Geocoding {

    /**
     * Geocode an address using Google Maps API
     */
    public static function geocode_address($address) {
        // Get the API key
        $api_key = get_option('fvl_google_maps_api_key');
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => __('Google Maps API key is not configured.', 'farmer-vending-locations')
            );
        }
        
        // URL encode the address
        $address = urlencode($address);
        
        // Create the API URL
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$api_key}";
        
        // Get the response
        $response = wp_remote_get($url);
        
        // Check for errors
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        // Decode the response
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        // Check if the request was successful
        if ($data['status'] !== 'OK') {
            return array(
                'success' => false,
                'message' => isset($data['error_message']) ? $data['error_message'] : $data['status']
            );
        }
        
        // Get the first result
        $result = $data['results'][0];
        
        // Return the geocoded data
        return array(
            'success' => true,
            'data' => array(
                'formatted_address' => $result['formatted_address'],
                'latitude' => $result['geometry']['location']['lat'],
                'longitude' => $result['geometry']['location']['lng'],
            )
        );
    }
}