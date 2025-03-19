(function($) {
    'use strict';

    // Initialize when the DOM is ready
    $(document).ready(function() {
        // Media uploader for gallery
        initGalleryUploader();
        
        // Google Maps for location
        initGoogleMap();
        
        // Geocoding functionality
        initGeocoding();
        
        // Review management
        initReviewManagement();
        
        // Settings tabs
        initSettingsTabs();
    });

    /**
     * Initialize the media uploader for gallery images
     */
    function initGalleryUploader() {
        var galleryFrame;
        var galleryContainer = $('.fvl-gallery-container');
        var galleryInput = $('#fvl_gallery');
        
        // Add images button
        $('#fvl_add_gallery_images').on('click', function(e) {
            e.preventDefault();
            
            // If the frame already exists, reopen it
            if (galleryFrame) {
                galleryFrame.open();
                return;
            }
            
            // Create a new media frame
            galleryFrame = wp.media({
                title: 'Select or Upload Images',
                button: {
                    text: 'Add to Gallery'
                },
                multiple: true
            });
            
            // When images are selected, add them to the gallery
            galleryFrame.on('select', function() {
                var selection = galleryFrame.state().get('selection');
                var currentIds = galleryInput.val() ? galleryInput.val().split(',') : [];
                
                selection.map(function(attachment) {
                    attachment = attachment.toJSON();
                    
                    // Only add if not already in the gallery
                    if ($.inArray(attachment.id.toString(), currentIds) === -1) {
                        currentIds.push(attachment.id.toString());
                        
                        // Add image to the gallery
                        galleryContainer.append(
                            '<div class="fvl-gallery-image" data-id="' + attachment.id + '">' +
                                '<img src="' + (attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url) + '" alt="" />' +
                                '<span class="fvl-remove-image dashicons dashicons-no-alt"></span>' +
                            '</div>'
                        );
                    }
                });
                
                // Update the input field
                galleryInput.val(currentIds.join(','));
            });
            
            // Open the frame
            galleryFrame.open();
        });
        
        // Remove image button
        galleryContainer.on('click', '.fvl-remove-image', function() {
            var image = $(this).parent();
            var id = image.data('id').toString();
            var currentIds = galleryInput.val().split(',');
            
            // Remove the ID from the array
            currentIds = currentIds.filter(function(value) {
                return value !== id;
            });
            
            // Update the input field
            galleryInput.val(currentIds.join(','));
            
            // Remove the image
            image.remove();
        });
    }
    
    /**
     * Initialize Google Map for the location
     */
    function initGoogleMap() {
        var mapContainer = $('#fvl_map');
        
        // If map container doesn't exist or Google Maps API is not loaded, return
        if (mapContainer.length === 0 || typeof google === 'undefined' || typeof google.maps === 'undefined') {
            return;
        }
        
        var latitude = parseFloat($('#fvl_latitude').val()) || 0;
        var longitude = parseFloat($('#fvl_longitude').val()) || 0;
        
        // Create map
        var map = new google.maps.Map(mapContainer[0], {
            center: { lat: latitude, lng: longitude },
            zoom: 14
        });
        
        // Create marker if coordinates exist
        var marker = null;
        if (latitude !== 0 && longitude !== 0) {
            marker = new google.maps.Marker({
                position: { lat: latitude, lng: longitude },
                map: map,
                draggable: true
            });
            
            // Update coordinates when marker is dragged
            google.maps.event.addListener(marker, 'dragend', function() {
                var position = marker.getPosition();
                $('#fvl_latitude').val(position.lat());
                $('#fvl_longitude').val(position.lng());
            });
        }
        
        // Click on map to place marker
        google.maps.event.addListener(map, 'click', function(event) {
            var position = event.latLng;
            
            if (marker === null) {
                marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    draggable: true
                });
                
                // Update coordinates when marker is dragged
                google.maps.event.addListener(marker, 'dragend', function() {
                    var position = marker.getPosition();
                    $('#fvl_latitude').val(position.lat());
                    $('#fvl_longitude').val(position.lng());
                });
            } else {
                marker.setPosition(position);
            }
            
            $('#fvl_latitude').val(position.lat());
            $('#fvl_longitude').val(position.lng());
        });
    }
    
    /**
     * Initialize geocoding functionality
     */
    function initGeocoding() {
        $('#fvl_geocode_address').on('click', function(e) {
            e.preventDefault();
            
            var address = $('#fvl_address').val();
            
            if (!address) {
                alert('Please enter an address first.');
                return;
            }
            
            // Disable the button
            $(this).prop('disabled', true).text('Geocoding...');
            
            // Send AJAX request
            $.ajax({
                url: fvlAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fvl_geocode_address',
                    nonce: fvlAdmin.nonce,
                    address: address
                },
                success: function(response) {
                    // Re-enable the button
                    $('#fvl_geocode_address').prop('disabled', false).text('Geocode Address');
                    
                    if (response.success) {
                        // Update the address field
                        $('#fvl_address').val(response.data.formatted_address);
                        
                        // Update the coordinates
                        $('#fvl_latitude').val(response.data.latitude);
                        $('#fvl_longitude').val(response.data.longitude);
                        
                        // Refresh the map
                        initGoogleMap();
                        
                        alert('Address geocoded successfully.');
                    } else {
                        alert('Geocoding failed: ' + response.data.message);
                    }
                },
                error: function() {
                    // Re-enable the button
                    $('#fvl_geocode_address').prop('disabled', false).text('Geocode Address');
                    
                    alert('An error occurred. Please try again.');
                }
            });
        });
    }
    
    /**
     * Initialize review management functionality
     */
    function initReviewManagement() {
        // Approve review
        $('.fvl-approve-review').on('click', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var reviewId = button.data('id');
            
            // Disable the button
            button.prop('disabled', true);
            
            // Send AJAX request
            $.ajax({
                url: fvlAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fvl_approve_review',
                    nonce: fvlAdmin.nonce,
                    review_id: reviewId
                },
                success: function(response) {
                    if (response.success) {
                        // Update the row
                        button.closest('tr').find('.fvl-review-status').text('Approved');
                        button.remove();
                    } else {
                        alert('Failed to approve review: ' + response.data.message);
                        button.prop('disabled', false);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    button.prop('disabled', false);
                }
            });
        });
        
        // Delete review
        $('.fvl-delete-review').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to delete this review?')) {
                return;
            }
            
            var button = $(this);
            var reviewId = button.data('id');
            
            // Disable the button
            button.prop('disabled', true);
            
            // Send AJAX request
            $.ajax({
                url: fvlAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fvl_delete_review',
                    nonce: fvlAdmin.nonce,
                    review_id: reviewId
                },
                success: function(response) {
                    if (response.success) {
                        // Remove the row
                        button.closest('tr').remove();
                    } else {
                        alert('Failed to delete review: ' + response.data.message);
                        button.prop('disabled', false);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    button.prop('disabled', false);
                }
            });
        });
    }
    
    /**
     * Initialize settings tabs
     */
    function initSettingsTabs() {
        $('.fvl-settings-tab').on('click', function(e) {
            e.preventDefault();
            
            var tabId = $(this).attr('href');
            
            // Show the active tab content
            $('.fvl-settings-tab-content').hide();
            $(tabId).show();
            
            // Update active tab
            $('.fvl-settings-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
        });
        
        // Show the first tab by default
        $('.fvl-settings-tab:first').click();
    }

})(jQuery);