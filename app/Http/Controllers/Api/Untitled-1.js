composer require laravel/passport
php artisan passport:install
php artisan db:migrate
php artisan passport:keys

php artisan vendor:publish --tag=passport-config

php artisan passport:client --personal


let response = pm.response.json();

// Check if the response contains the access token
if (response.success && response.data && response.data.access_token) {
    // Set the access token as an environment variable
    pm.environment.set("token", response.data.access_token);
    console.log("Token has been set in the environment");
} else {
    console.error("Token not found in the response");
}
