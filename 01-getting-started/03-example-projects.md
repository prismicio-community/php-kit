# Example Projects

This article will walk you through the best way to using Prismic with PHP applications. The example projects provide a collection of pre-built sample content, Custom Types, source code, and more. It will guide you on how to build an application and explore various features and capabilities of Prismic.

---

## Install the Prismic CLI

The first thing is to install the Prismic CLI (command line interface) if you haven't already. Launch the terminal (command prompt or similar on Windows) and run the following command.

**npm**:

```bash
npm install -g prismic-cli
```

**Yarn**:

```bash
yarn install -g prismic-cli
```

Sometimes it's possible that the above command doesn't work because of permission levels on your machine.

If that's the case for you, run the following sudo command:

**npm**:

```bash
sudo npm install -g prismic-cli
```

**Yarn**:

```bash
sudo yarn global add prismic-cli
```

> \***\*⚠️** Prismic CLI version\*\*
>
> Make sure you're using the latest version of [Prismic CLI](https://www.npmjs.com/package/prismic-cli). You can check your version by running `prismic --version` command in your terminal.

---

## Choose a project

You can choose any of the available sample projects. You can also see a preview of the project to check how the application works.

> **Prismic theme command**
>
> To install a sample project, run the theme command. The theme command clones the project locally, installs all necessary dependencies and creates a Prismic repository with Custom Types and pre-built content. It will ask you to give the name of your Prismic repository and the local folder to initialize your project**. **

### Blog website

![](https://images.prismic.io/prismicio-docs-v3/58f521f8-ab0a-4991-b24f-3c9668710fa1_Blog-Image.png?auto=compress,format&rect=11,0,1656,1096&w=470&h=311)

This blog website is an excellent starting point to explore PHP and Prismic. Modify and adapt it to your liking.

- [Preview](http://sample-prismic-blog.herokuapp.com/)
- [Explore](https://github.com/prismicio/php-blog/archive/refs/heads/master.zip)

**Run the following Prismic theme command in the terminal to install this project locally:**

```bash
prismic theme --theme-url https://github.com/prismicio/php-blog --conf config.php
```

---

### Business website

![](https://images.prismic.io/prismicio-docs-v3/3e1a4570-f9fd-442b-aa39-c061bcc720b8_sample-website.png?auto=compress,format&rect=11,0,1656,1096&w=470&h=311)

A Business website with multiple pages and a dynamic menu. This project provides all the code you need for a website with a homepage, information pages, and navigation

- [Preview](https://website-sample.herokuapp.com/)
- [Explore](https://github.com/prismicio/php-website)

**Run the following Prismic theme command in the terminal to install this project locally:**

```bash
prismic theme --theme-url https://github.com/prismicio/php-website --conf config.php
```

> **Verify repository name**
>
> Open prismic-configuration.js file and verify the prismic repo matches the URL of the Prismic repository created earlier in this article. To find this, go to your [Prismic dashboard](https://prismic.io/dashboard/), then to your repository.
>
> If the URL for your repository is https://my-awesome-repository.prismic.io, then you'll need to replace your-repo-name with my-awesome-repository.

---

## Install dependencies

First, you'll need to install the dependencies for the project. Run the following command.

```plaintext
composer install
```

> Note that you will need to make sure to first have [Composer](https://getcomposer.org/) installed before running the above command. Check out the [Composer Getting Started](https://getcomposer.org/doc/00-intro.md) page for installation instructions.

## Run the website in development mode

To launch a local development server at [http://localhost:8080](http://localhost:8080/) run the following command:

```bash
./serve.sh
```

Now you can customize the code and content however you want, and deploy your project when you're ready.

---

## Related articles

- [**Deployment**](./deployment-php.md)<br/>Learn how to deploy your PHP app and rebuild your site when you update your content.

- [**Previews and the Prismic toolbar**](../04-beyond-the-api/02-previews-and-the-prismic-toolbar.md)<br/>Learn how to preview content changes without publishing your document or rebuilding your project.
