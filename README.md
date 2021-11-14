# Test assignment for ClickLogiq

## Assignment

This test requires:
* Working with PHP7.* and Symfony 4+
* Mysql Database
* Use Symfony coding standards
* Nginx or Apache

### Required Functionality:

1. Implement a call to NASA API to retrieve the list of Asteroids based on their closest
approach date to Earth (Near-Earth Objects - NEOs):
	* Use api.nasa.gov
	* API-KEY: (hidden, can be provided separately)
	* Documentation: https://api.nasa.gov/ and go to "Browse APIs -> Asteroids - NeoWs"
	* Limit the query to the last 3 days.

2. Persist the resulting list in your DB without duplicates.
Define the data model as follows:
	* date
	* reference (neo_reference_id)
	* name
	* speed (kilometers_per_hour)
	* is hazardous (is_potentially_hazardous_asteroid)

3. Create the following routes in your application:
	<ul>
		<li><b>GET /neo/hazardous</b>
			<ul>
				<li>return all DB entries which contain potentially hazardous asteroids (with pagination)</li>
				<li>response format: JSON</li>
			</ul>
		<li><b>GET /neo/fastest?hazardous=(true|false)</b></li>
		<ul>
			<li>calculate and return the model of the fastest asteroid</li>
			<li>with a hazardous parameter, where true means is hazardous</li>
			<li>default hazardous value is false</li>
			<li>response format: JSON</li>
		</ul>
		<li><b>GET /neo/best-month?hazardous=(true|false)</b></li>
		<ul>
			<li>calculate and return a month with most asteroids (not a month in a year)</li>
			<li>with a hazardous parameter, where true means is hazardous</li>
			<li>default hazardous value is false</li>
			<li>response format: JSON</li>
		</ul>
	</ul>
	
4. Bonus points:
	* Clean and straightforward code
	* Use restful API best practices
	* Create functional tests for API endpoints.
	* Use Docker

## Solution

Technologies:
* PHP 7.4
* Symfony 5.1
* Nginx+php-fpm
* MySQL 5.7
* Docker (docker-compose)

### Notice

1. Project setup in Docker will be refactored
2. Functional tests are not yet implemented

### Project setup

1. Copy .env.dist to .env to setup docker variables

		HOST_USER=1000:1000
		MYSQL_DATABASE=clicklogic_test_task
		MYSQL_ROOT_PASSWORD=password
		MYSQL_PASSWORD=password
		MYSQL_USER=root
		COMPOSER_AUTH='{"github-oauth":{"github.com":"<personal access token>"}}'
		
2. Copy app.env.dist to app/.env.local to setup application variables and set proper variables
3. Run 
		
		docker-compose build
		docker-compose up -d
		
4. Go inside php-fpm container and install composer dependencies
		
		docker-compose exec php-fpm bash
		composer install
	
5. Inside php-fpm container run
		
		php bin/console nasa-api:parse-near-earth-objects

6. Download Postman collection and environment (in the postman folder),
set NASA API key in environment variables and check endpoints.