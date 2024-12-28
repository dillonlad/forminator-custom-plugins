jQuery(document).ready(function($) {
    // Run only if the form with the specific ID is present
    if ($(`#forminator-module-${forminatorFormID.id}`).length) {
        // Fetch data from the custom API endpoint
        $.get('/wp-json/custom/v1/get-select-options', function(data) {
            // Replace 'select-1' with your Forminator field name or ID
            //let selectField = $('select[name="select-2"]');  
            const multiSelect = document.querySelector('select[name="select-2[]"]');
            // Clear existing options
            $(multiSelect).empty();

            // Populate the select field with new options from API response
            data.forEach(option => {
                const selectOption = document.createElement('option');
                selectOption.value = option.value;
                selectOption.textContent = option.label;

                // Append the option to the multi-select field
                multiSelect.appendChild(selectOption);
            });
        });
    }
});
