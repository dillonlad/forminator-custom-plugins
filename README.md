# forminator-custom-plugins
Custom forminator plugins for handling specific cases within WordPress

## Custom Forminator Dropdown
Populates a forminator multi-select dropdown from a specific table in the database once the form is loaded.

## Forminator File S3 Upload
Files uploaded via forms on this website can't be stored locally for security reasons and must be uploaded to an Amazon S3 bucket. This plugin requires the aws-sdk-php plugin to be installed using composer.

## Forminator Spam Prevention
Forminator's supported spam prevention (Google reCaptcha, Askitmet...) just doesn't work well enough for this site. I've added additional spam prevention methods to detect the language and reject it if it's not English (the website is for a small UK-based company). The method also uses a regex to prevent any links being submitted in the textarea, and also another regex to ensure only valid email addresses are used.