jQuery(document).ready(function($) {
    // Run only if the form with the specific ID is present
    if ($(`#forminator-module-${forminatorFormID.id}`).length) {
        const multiSelect = document.querySelector('select[name="select-2[]"]');
        let allOptions = [];  // Array to store all options
        let optionsLoaded = false;  // Flag to ensure only one API call
        console.log("here", multiSelect)

        // Function to filter and display options based on input
        function filterOptions(query) {
            // Clear existing options
            $(multiSelect).empty();

            // Filter options based on user input (case-insensitive)
            const filteredOptions = allOptions.filter(option => 
                option.label.toLowerCase().includes(query.toLowerCase())
            );

            console.log("##filteredopt", filterOptions)

            // Populate the select field with filtered options
            filteredOptions.forEach(option => {
                const selectOption = document.createElement('option');
                selectOption.value = option.value;
                selectOption.textContent = option.label;
                multiSelect.appendChild(selectOption);
            });
        }

        // Function to initialize options loading and filtering on dropdown open
        function initializeFiltering() {
            // Use aria-controls attribute to locate the input field once the dropdown is visible
            const filterInput = document.querySelector('.select2-search__field');
            console.log("filtinput", filterInput)
            if (filterInput && !optionsLoaded) {
                // Load all options on the first interaction with the filter input
                $(filterInput).one('focus', function() {
                    $.get('/wp-json/custom/v1/get-select-options', function(data) {
                        // Store all options in memory
                        allOptions = data;
                        optionsLoaded = true;
                    });
                });

                // Set up an event listener for input on the filter input
                $(filterInput).on('input', function() {
                    const query = this.value;
                    console.log("query", query)
                    // Filter options if the input has at least 3 characters
                    if (query.length >= 3) {
                        filterOptions(query);
                    } else {
                        // Clear options if input is less than 3 characters
                        $(multiSelect).empty();
                    }
                });
            }
        }

        // Observe changes to the dropdown container's aria-hidden attribute
        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                if (mutation.attributeName === 'aria-hidden') {
                    const dropdownContainer = mutation.target;
                    // Check if the dropdown is now visible
                    if (dropdownContainer.getAttribute('aria-hidden') === 'false') {
                        initializeFiltering(); // Call initialization when dropdown is shown
                    }
                }
            });
        });

        // Start observing the dropdown container for attribute changes
        const dropdownContainer = document.querySelector('.select2-container'); // Adjust selector as needed
        if (dropdownContainer) {
            observer.observe(dropdownContainer, { attributes: true });
        }
    }
});
