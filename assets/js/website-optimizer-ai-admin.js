(function($) {
    'use strict';

    $(document).ready(function() {
        function validateSiteId(siteId) {
            return /^[a-zA-Z0-9-_]{6,}$/.test(siteId);
        }

        function toggleSiteIdField() {
            var isEnabled = $('#enabled').is(':checked');
            $('#site_id').prop('disabled', !isEnabled);
            if (isEnabled) {
                $('#site_id').closest('tr').css('opacity', 1);
            } else {
                $('#site_id').closest('tr').css('opacity', 0.5);
            }
        }

        function extractSiteId(input) {
            var match = input.match(/data-site="([^"]+)"/);
            return match ? match[1] : input;
        }

        toggleSiteIdField();

        $('#enabled').on('change', toggleSiteIdField);

        $('#site_id').on('paste', function(e) {
            e.preventDefault();
            var pastedData = e.originalEvent.clipboardData.getData('text');
            var siteId = extractSiteId(pastedData);
            $(this).val(siteId);
            validateAndUpdateMessage(siteId);
        });

        $('#woai-settings-form').on('submit', function(e) {
            var siteId = $('#site_id').val();
            var isEnabled = $('#enabled').is(':checked');
            
            if (isEnabled && !validateSiteId(siteId)) {
                e.preventDefault();
                alert('Please enter a valid Site ID.');
            }
        });

        function validateAndUpdateMessage(siteId) {
            var validationMessage = $('#site-id-validation');
            
            if (!validationMessage.length) {
                $('#site_id').after('<span id="site-id-validation"></span>');
                validationMessage = $('#site-id-validation');
            }

            if (validateSiteId(siteId)) {
                validationMessage.text('Site ID looks good').css('color', 'green');
            } else {
                validationMessage.text('Invalid Site ID').css('color', 'red');
            }
        }

        $('#site_id').on('input', function() {
            var siteId = $(this).val();
            validateAndUpdateMessage(siteId);
        });
    });

})(jQuery);