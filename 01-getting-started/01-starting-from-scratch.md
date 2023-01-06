# Start from Scratch

Prismic makes it easy to get started on a new PHP project by providing a specific PHP starter project kit.

> **Just getting started with Prismic?**
>
> If you're just getting started with Prismic we recommend first checking out our [PHP Tutorial Example](https://prismic.io/quickstart#?lang=php) to see how to get a repository set up and a simple website running in minutes.

## Create a content repository

A content repository is where you can define, edit, and publish your website content.

[**Create a new repository**](https://prismic.io/dashboard/new-repository/)

## Download the PHP starter kit

The starter kit allows you to query and retrieve content from your Prismic content repository and integrate it into your website templates. It's the easiest way to get started with a new project.

[**Download the starter kit**](https://github.com/prismicio/php-quickstart/archive/starter.zip)

Unzip the downloaded project files into the desired location for your new project.

## Configure your project

Replace the repository url in your Prismic configuration file (config.php) with your repository's API endpoint.

**php (sdk v4 or later)**:

```
// In config.php
define('PRISMIC_URL', 'https://your-repo-name.cdn.prismic.io/api/v2');
```

**php (sdk v3 or earlier)**:

```
// In config.php
define('PRISMIC_URL', 'https://your-repo-name.cdn.prismic.io/api');
```

Fire up a terminal (command prompt or similar on Windows), point it to your project location and run the following commands!

> Note that you will need to make sure to first have [Composer](https://getcomposer.org/) installed before running this command. Check out the [Composer Getting Started](https://getcomposer.org/doc/00-intro.md) page for installation instructions.

First you'll need to install the dependencies for the project. Run the following command.

```bash
composer install
```

Now you can to launch your local server.

```bash
./serve.sh
```

You can now open your browser to [http://localhost:8000](http://localhost:8000), where you will find a tutorial page. This page contains information helpful to getting started. You will learn how to query the API and start building pages for your new site.

> **Pagination of API Results**
>
> When querying a Prismic repository, your results will be paginated. By default, there are 20 documents per page in the results. You can read more about how to manipulate the pagination in the [Pagination for Results](../02-query-the-api/18-pagination-for-results.md) page.

## And your Prismic journey begins!

Now you're all set to start building your new website with the Prismic content management system. Here are the next steps you need to take.

### Define your Custom Types

First you will need to model your pages, blog posts, articles, etc. into different Custom Types. Refer to our documentation to learn more about [constructing your Custom Types](https://intercom.help/prismicio/content-modeling-and-custom-types) using our easy drag and drop builder.

### Query your documents

After you have created and published some documents in your content repository, you will be able to query your API and retrieve the content. We provide explanations and plenty of examples of queries in the documentation. Start by learning more on the [How to Query the API](../02-query-the-api/01-how-to-query-the-api.md) page.

### Integrate content into your templates

The final step will be to integrate your content into the website templates. Check out the [templating documentation](../03-templating/01-the-response-object.md) to learn how to integrate each content field type.
