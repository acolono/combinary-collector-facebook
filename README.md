# Combinary Facebook Collector
An application using the Facebook Graph API to collect information from user owned pages and displaying metrics in a dashboard.

For more application details (screenshots) and other services please go to: https://app.gitbook.com/@acolono/s/combinary/

## Setup

### Developer

The app uses a docker-compose file as a base with a services.yml file.

1. Clone the git repo and run it where you want to.
2. Add the dev URL if you are in development and set the Development variable to true.
3. For local development it is recommended to use NGROk as Facebook is strict with where callback requests are sent.

### User

1. Login to the application using your Facebook credentials.
2. Select the pages you would like to import and then monitor using a webhook.
3. If you would like to give new permissions for new pages then logout and log back in to select the required pages.
4. Once imported the webhooks are setup and new posts will be added to the database.
