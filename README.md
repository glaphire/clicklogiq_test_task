# Test assignment for ClickLogiq

## Assignment
### Requirements

This test requires:
* Working with PHP7.* and Symfony 4+
* Mysql Database
* Use Symfony coding standards
* Nginx or Apache

Required Functionality:

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
(TODO: write how to setup project)


