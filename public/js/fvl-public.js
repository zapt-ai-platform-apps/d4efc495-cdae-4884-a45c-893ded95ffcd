(function($) {
    'use strict';

    // Initialize when the DOM is ready
    $(document).ready(function() {
        // Initialize maps
        initMaps();
        
        // Initialize review form
        initReviewForm();
        
        // Initialize location filters
        initLocationFilters();
        
        // Initialize location gallery
        initGallery();
    });

    /**
     * Initialize Google Maps
     */
    function initMaps() {
        // Map shortcode
        initMapShortcode();
        
        // Location detail map
        initDetailMap();
    }
    
    /**
     * Initialize map from shortcode
     */
    function initMapShortcode() {
        $('.fvl-map-container').each(function() {
            var mapContainer = $(this);
            var mapId = mapContainer.attr('id');
            
            // If Google Maps API is not loaded, return
            if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                mapContainer.html('<div class="fvl-map-error">Google Maps API is not loaded. Please check your API key.</div>');
                return;
            }
            
            // Create the map
            var map = new google.maps.Map(document.getElementById(mapId), {
                center: { lat: 0, lng: 0 },
                zoom: parseInt(fvlPublic.mapZoom) || 10,
                styles: fvlPublic.mapStyle !== 'default' ? JSON.parse(fvlPublic.mapStyle) : null
            });
            
            // Store the markers
            var markers = [];
            var bounds = new google.maps.LatLngBounds();
            var infoWindow = new google.maps.InfoWindow();
            
            // Get the locations
            $.ajax({
                url: fvlPublic.ajaxUrl,
                type: 'GET',
                data: {
                    action: 'fvl_get_locations',
                    nonce: fvlPublic.nonce,
                    category: fvlMapData && fvlMapData.category ? fvlMapData.category : '',
                    payment: fvlMapData && fvlMapData.payment ? fvlMapData.payment : ''
                },
                success: function(response) {
                    if (response.success && response.data.locations.length > 0) {
                        // Add markers for each location
                        response.data.locations.forEach(function(location) {
                            var position = new google.maps.LatLng(location.latitude, location.longitude);
                            
                            // Create marker
                            var marker = new google.maps.Marker({
                                position: position,
                                map: map,
                                title: location.title,
                                icon: fvlPublic.mapMarkerIcon || null
                            });
                            
                            // Extend bounds
                            bounds.extend(position);
                            
                            // Create info window content
                            var content = '<div class="fvl-map-info-window">' +
                                '<h3>' + location.title + '</h3>';
                            
                            if (location.thumbnail) {
                                content += '<div class="fvl-map-info-image"><img src="' + location.thumbnail + '" alt="' + location.title + '" /></div>';
                            }
                            
                            content += '<div class="fvl-map-info-address">' + location.address + '</div>';
                            
                            if (location.rating > 0) {
                                content += '<div class="fvl-map-info-rating">';
                                content += '<span class="fvl-star-rating">';
                                for (var i = 1; i <= 5; i++) {
                                    if (i <= Math.floor(location.rating)) {
                                        content += '★';
                                    } else if (i - 0.5 <= location.rating) {
                                        content += '☆';
                                    } else {
                                        content += '☆';
                                    }
                                }
                                content += '</span>';
                                content += ' ' + location.rating + ' (' + location.reviewCount + ' reviews)';
                                content += '</div>';
                            }
                            
                            content += '<a href="' + location.permalink + '" class="fvl-map-info-link">View Details</a>';
                            content += '</div>';
                            
                            // Add click listener
                            marker.addListener('click', function() {
                                infoWindow.setContent(content);
                                infoWindow.open(map, marker);
                            });
                            
                            // Add to markers array
                            markers.push(marker);
                        });
                        
                        // Fit map to bounds
                        map.fitBounds(bounds);
                        
                        // If only one marker, zoom out a bit
                        if (markers.length === 1) {
                            var listener = google.maps.event.addListener(map, 'idle', function() {
                                map.setZoom(14);
                                google.maps.event.removeListener(listener);
                            });
                        }
                    } else {
                        // No locations found
                        mapContainer.html('<div class="fvl-map-error">No locations found.</div>');
                    }
                },
                error: function() {
                    mapContainer.html('<div class="fvl-map-error">Error loading locations. Please try again.</div>');
                }
            });
        });
    }
    
    /**
     * Initialize map on location detail page
     */
    function initDetailMap() {
        var detailMap = $('.fvl-detail-map');
        
        if (detailMap.length === 0 || typeof google === 'undefined' || typeof google.maps === 'undefined') {
            return;
        }
        
        detailMap.each(function() {
            var mapContainer = $(this);
            var latitude = parseFloat(mapContainer.data('latitude')) || 0;
            var longitude = parseFloat(mapContainer.data('longitude')) || 0;
            
            if (latitude === 0 && longitude === 0) {
                return;
            }
            
            // Create map
            var map = new google.maps.Map(mapContainer[0], {
                center: { lat: latitude, lng: longitude },
                zoom: 14,
                styles: fvlPublic.mapStyle !== 'default' ? JSON.parse(fvlPublic.mapStyle) : null
            });
            
            // Add marker
            new google.maps.Marker({
                position: { lat: latitude, lng: longitude },
                map: map,
                icon: fvlPublic.mapMarkerIcon || null
            });
        });
    }
    
    /**
     * Initialize review form
     */
    function initReviewForm() {
        // Rating stars
        $('.fvl-rating-star').on('click', function() {
            var rating = $(this).data('rating');
            $('#fvl_rating').val(rating);
            
            // Update stars
            $('.fvl-rating-star').removeClass('active');
            $('.fvl-rating-star').each(function() {
                if ($(this).data('rating') <= rating) {
                    $(this).addClass('active');
                }
            });
        });
        
        // Submit review
        $('.fvl-review-form').on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var submitButton = form.find('button[type="submit"]');
            var rating = form.find('#fvl_rating').val();
            
            // Validate rating
            if (!rating) {
                alert('Please select a rating.');
                return;
            }
            
            // Disable the button
            submitButton.prop('disabled', true).text('Submitting...');
            
            // Get form data
            var formData = {
                action: 'fvl_submit_review',
                nonce: fvlPublic.nonce,
                location_id: form.find('#fvl_location_id').val(),
                rating: rating,
                review: form.find('#fvl_review_text').val()
            };
            
            // Submit the review
            $.ajax({
                url: fvlPublic.ajaxUrl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        form.html('<div class="fvl-review-success">' + response.data.message + '</div>');
                    } else {
                        // Show error message
                        alert(response.data.message);
                        submitButton.prop('disabled', false).text('Submit Review');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    submitButton.prop('disabled', false).text('Submit Review');
                }
            });
        });
    }
    
    /**
     * Initialize location filters
     */
    function initLocationFilters() {
        $('.fvl-filter-select').on('change', function() {
            var category = $('#fvl_filter_category').val();
            var payment = $('#fvl_filter_payment').val();
            
            // Reload the page with the selected filters
            var url = new URL(window.location.href);
            
            if (category) {
                url.searchParams.set('category', category);
            } else {
                url.searchParams.delete('category');
            }
            
            if (payment) {
                url.searchParams.set('payment', payment);
            } else {
                url.searchParams.delete('payment');
            }
            
            window.location.href = url.toString();
        });
    }
    
    /**
     * Initialize location gallery
     */
    function initGallery() {
        $('.fvl-gallery-item').on('click', function() {
            var imageUrl = $(this).data('full');
            var title = $(this).data('title');
            
            if (imageUrl) {
                // Create a lightbox
                var lightbox = $('<div class="fvl-lightbox"></div>');
                var lightboxContent = $('<div class="fvl-lightbox-content"></div>');
                var lightboxImage = $('<img src="' + imageUrl + '" alt="' + title + '" />');
                var lightboxClose = $('<span class="fvl-lightbox-close">&times;</span>');
                
                // Append to lightbox
                lightboxContent.append(lightboxImage);
                lightboxContent.append(lightboxClose);
                lightbox.append(lightboxContent);
                
                // Append to body
                $('body').append(lightbox);
                
                // Close lightbox on click
                lightbox.on('click', function(e) {
                    if ($(e.target).is(lightbox) || $(e.target).is(lightboxClose)) {
                        lightbox.remove();
                    }
                });
                
                // Close on escape key
                $(document).on('keydown.fvlLightbox', function(e) {
                    if (e.keyCode === 27) {
                        lightbox.remove();
                        $(document).off('keydown.fvlLightbox');
                    }
                });
            }
        });
    }

})(jQuery);