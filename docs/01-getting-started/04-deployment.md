# Deployement

This article explains how to deploy your PHP application live in your own production environment.

---

## Deploy the demo to Heroku

To view the demo application live in your own production environment, follow these steps:

### 1. Create a Heroku account

To create a new Heroku account, follow the link: [https://signup.heroku.com/](https://signup.heroku.com/).

### 2. Login to Heroku from the terminal

```
heroku login
```

### 3. Create a new Heroku application

```
heroku create
```

### 4. Add Heroku remote

```
# Add heroku remote
heroku git:remote -a <name-of-the-newly-created-app>

# Check the remotes available
# Newly added `heroku` remote repository connection should be shown
git remote -v
```

### 5. Commit your changes

```
# Commit the most recent work on the development branch
git add .
git commit -m 'Write a clear meaningful commit message here'
```

### 6. Deploy to Heroku:

```
git push heroku master
```

### 7. Open the app in the browser

```
heroku open
```
