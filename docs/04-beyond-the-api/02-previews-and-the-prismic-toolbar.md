# Previews and the Prismic Toolbar

When working in the writing room, you can preview new content on your website without having to publish the document, you can set up one or more websites where the content can be previewed. This allows you to have a production server, staging server, development server, etc.  Discover how to [handle multiple environments in Prismic](https://intercom.help/prismicio/prismic-io-basics/using-multiple-environments-of-one-prismic-repository).<br/>
The Toolbar allows you to edit your pages. In your website, just click the button, select the part of the page that they want to edit, and you'll be redirected to the appropriate page in the Writing Room.

> **New Toolbar Activation for Older Repos**
>
> The current preview toolbar is enabled by default on all new Prismic repos. However, if you're on an older repo, you might be on older version of the toolbar. If your preview script `src` url includes `new=true`, then your preview functionality includes an edit button. If the `src` does not include `new=true` and you would like the new preview toolbar, please [contact us via the Prismic forum](https://community.prismic.io/t/feature-activations-graphql-integration-fields-etc/847) so we can activate it for you.

## 1. Configure Previews

In your repository, navigate to **Settings > Previews**. Here you can add the configuration for a new preview, just add:

- **The Site Name**: The name of the current site your configuring.
- **Domain URL**: You can add the URL of your live website or a localhost domain, such as: http://localhost:8000.
- **Link Resolver (optional) **: In order to be taken directly to the page you are previewing instead of the homepage, add a Link Resolver which is an endpoint on your server that takes a Prismic document and returns the url for that document. More details on step _3. Add a Link Resolver endpoint._

![](https://images.prismic.io/prismicio-docs-v3/ZDViMDc0MWMtMDBhZC00YmNkLWIwZjMtNjgxMGQ2ZTNiNTZj_7090417a-cf3f-457d-8229-2f8bbc7af4aa_screenshot2020-09-13at20.34.27.pngautocompressformatrect00954834w700h612?auto=compress,format&rect=0,0,700,612&w=960&h=839)

## 2. Include the Prismic Toolbar javascript file

You will need to include the Prismic toolbar script **on every page of your website including your 404 page.**

You can find the correct script in your repository **Settings** section, under the **Previews** tab.

**Settings > Previews > Script**

```
<script async defer src="https://static.cdn.prismic.io/prismic.js?new=true&repo=your-repo-name”></script>
```

> **Correct repo name**
>
> Note: This example script has your-repo-name at the end of the URL, this value needs to be replaced with your repository name. You can find the correct script for in your repository's **Settings > Previews > Script.**

> **Shareable Previews & unpublished previews**
>
> To guarantee that Shearable Preview links and unpublished document previews work properly **you must ensure that these scripts are included on every page of your website, including your 404/Page Not Found page**. Otherwise these previews might not work.

## 3. Add a Link Resolver endpoint

In order to be taken directly to the page you are previewing instead of the homepage, you need to add a Link Resolver endpoint. A typical example of this would be:

```
https://{yoursite}/preview
```

In your preview settings add an endpoint to the optional Link Resolver field as explained in step 1.

> **Using an official Prismic starter project?**
>
> If you are using our official Prismic PHP development kit ([php-kit](https://github.com/prismicio/php-kit)) or any other of our starter projects, then you should already have all the code in place that you need for Previews and the Prismic Toolbar!<br/>
> If you are not using this kit to make your queries, then follow the rest of the steps below.

Now you need to add the Link Resolver endpoint in your website application. When requested this endpoint must:

- Retrieve the preview token from the `token` parameter in the query string
- Call the Prismic development kit with this token and the [Link Resolver](../04-beyond-the-api/01-link-resolving.md) will retrieve the correct URL for the document being previewed
- Redirect to the given URL

> **The Preview Token**
>
> Note that the preview token will be a **URL**. You **DO NOT** need to follow this url. All you need to do is pass this token into the `previewSession` method as shown below

```
<?php
$token = isset($_GET['token']) ? $_GET['token'] : null;
if (!isset($token)) {
    header('HTTP/1.1 400 Bad Request', true, 400);
    exit('Bad Request');
}
$url = $api->previewSession($token, $linkResolver, '/');
header('Location: ' . $url);
```

The example above uses a Link Resolver function stored in the variable `$linkResolver` to determine the end url to redirect to. To learn more about how to set this up, check out our [Link Resolving](../04-beyond-the-api/01-link-resolving.md) page.

## 4. Use the correct reference

> **Are you using our php-kit?**
>
> This last step is only required if you are not using the [php-kit](https://github.com/prismicio/php-kit) to retrieve your API object.

If you are using the `Api::get` method to retrieve your API object, then the correct reference will automatically be used and this last step is not necessary. If you **are not** using this method, then do the following:

When you preview your website, a preview cookie is generated that contains the preview token. This token can be used as a valid ref to make Prismic API queries. For any query you make on your website, make sure to check for the Preview cookie and use this preview ref in the query options if the preview cookie exists.

Here is how to retrieve the correct ref:

```
// Example ref selection for PHP - This is already done with the PHP kit
function getPreviewRef() {
    $cookieNames = [
        str_replace(['.',' '], '_', Api::PREVIEW_COOKIE),
        Api::PREVIEW_COOKIE,
    ];
    foreach ($cookieNames as $cookieName) {
        if (isset($_COOKIE[$cookieName])) {
            return $_COOKIE[$cookieName];
        }
    }
    return null;
}

$previewRef = getPreviewRef();
$ref = $previewRef ? $previewRef : $api->master()->getRef();
```

Then here is an example that shows how to use this ref in your query:

```
<?php
$response = $api->query(
    Predicates::at('document.type', 'blog-post'),
    [ 'ref' => $ref ]
);
// $response contains the response object
```

And with that you should have everything in place that you need to begin previewing your content!

## 5. Troubleshooting

Mistakes happen. So sometimes you might to do a little troubleshooting to figure out where exactly the problem is from, [luckily we've created an article for just that](https://user-guides.prismic.io/en/articles/3403530-troubleshooting-previews).

> **Deprecated: In-Website Edit Button**
>
> Note: The In-Website Edit Button has been deprecated in favor of the Preview Toolbar, which has an edit button built in.
