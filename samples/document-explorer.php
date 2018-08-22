<?php

declare(strict_types=1);

/**
 * This is a simple script that displays the JSON structure for the selected document and a list of recently published
 * documents.
 *
 * To run it, first of all, change the PRISMIC_CONFIG constant below with the correct details for your repository and
 * then fire up a terminal and `cd` to this library's root directory and issue `composer serve`
 * This will start up PHP's built in web server on port 8080. To see it in action, visit http://localhost:8080
 *
 * If you want to run the script on a different port, issue the following command, replacing 8080 with your chosen port
 * php -S 0.0.0.0:8080 -t samples samples/document-explorer.php
 */


namespace Prismic\Sample {

    use Prismic\Api;
    use Prismic\Exception;
    use Prismic\SearchForm;
    use stdClass;

    /**
     * Provide the correct URL of your repository, and optionally,
     * a permanent access token if your API visibility is set to private.
     */
    const PRISMIC_CONFIG = [
        'api'   => 'https://your-repository-name.cdn.prismic.io/api/v2',
        'token' => null,
    ];

    require_once __DIR__ . '/../vendor/autoload.php';

    $finder = new Finder();
    echo (string) $finder;


    class Finder
    {
        /** @var Api */
        private $api;

        /** @var stdClass  */
        private $document;

        public function __construct()
        {
            try {
                $this->api = Api::get(PRISMIC_CONFIG['api'], PRISMIC_CONFIG['token']);
                $document = isset($_GET['id']) ? $this->findById($_GET['id']) : null;
                $document = $document ? $document : $this->mostRecent();
                $this->document = $document;
            } catch (Exception\ExceptionInterface $e) {
                $this->invalidRepo($e);
                exit;
            }
        }

        public function mostRecent() :? stdClass
        {
            /** @var SearchForm $form */
            $form = $this->api->forms()->everything;
            $form = $form->ref($this->api->ref());
            $form = $form->orderings('[document.last_publication_date desc]');
            $response = $form->submit();
            if (isset($response->results) && count($response->results) >= 1) {
                return current($response->results);
            }
            return null;
        }

        public function findById(string $id) :? stdClass
        {
            return $this->api->getByID($id);
        }

        public function __toString() : string
        {
            return $this->header() . $this->body() . $this->footer();
        }

        public function body() : string
        {
            $markup = [];
            $markup[] = '<div class="container">';
            $markup[] = '<div class="row my-4"><div class="col-md-12">';
            $title = sprintf(
                'Viewing Document ID# <code>%s</code> of type “%s”',
                $this->document->id,
                $this->document->type
            );
            $markup[] = sprintf('<h1>%s</h1>', $title);
            $markup[] = '</div></div>';
            $markup[] = '<div class="row"><div class="col-md-8">';
            $markup[] = $this->printJson();
            $markup[] = '</div>';
            $markup[] = '<div class="col-md-4">';
            $markup[] = $this->listLinks();
            $markup[] = $this->listRecentDocs();
            $markup[] = '</div></div></div>';
            return implode("\n", $markup);
        }

        private function printJson()
        {
            $markup = ['<pre>'];
            $markup[] = htmlentities(json_encode($this->document, JSON_PRETTY_PRINT));
            $markup[] = '</pre>';
            return implode("\n", $markup);
        }

        private function recentDocs(int $count = 10) : array
        {
            /** @var SearchForm $form */
            $form = $this->api->forms()->everything;
            $form = $form->ref($this->api->ref());
            $form = $form->orderings('[document.last_publication_date desc]');
            $response = $form->submit();
            $out = [];
            if (isset($response->results) && count($response->results) >= 1) {
                foreach ($response->results as $doc) {
                    $out[] = $doc;
                    if (count($out) >= $count) {
                        break;
                    }
                }
            }
            return $out;
        }

        private function listRecentDocs(int $count = 10) : string
        {
            $markup = [];
            $markup[] = '<div class="list-group mb-4">';
            $markup[] = '<h5 class="list-group-item list-group-item-action active">Recent Documents</h5>';
            foreach ($this->recentDocs($count) as $link) {
                $markup[] = sprintf(
                    '<a class="list-group-item list-group-item-action" href="/?id=%1$s">ID: %1$s, Type: %2$s</a>',
                    $link->id,
                    $link->type
                );
            }
            $markup[] = '</div>';
            return implode("\n", $markup);
        }

        private function listLinks() : string
        {
            $markup = [];
            $markup[] = '<div class="list-group mb-4">';
            $markup[] = '<h5 class="list-group-item list-group-item-action active">Linked Documents</h5>';
            $seen = [];
            foreach ($this->document->linked_documents as $link) {
                if (in_array($link->id, $seen)) {
                    continue;
                }
                $seen[] = $link->id;
                $markup[] = sprintf(
                    '<a class="list-group-item list-group-item-action" href="/?id=%1$s">ID: %1$s, Type: %2$s</a>',
                    $link->id,
                    $link->type
                );
            }
            $markup[] = '</div>';
            return implode("\n", $markup);
        }

        public function header() : string
        {
            return <<<'HEADER'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Prismic Document Browser</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4"
          crossorigin="anonymous">
</head>
<body>
<div class="container">
HEADER;
        }

        public function footer() : string
        {
            return '</div></body></html>';
        }

        private function invalidRepo(Exception\ExceptionInterface $e)
        {
            $markup = [];
            $markup[] = '<div class="alert alert-danger my-4"><h2>Failed to Retrieve Api Data</h2>';
            $markup[] = '<p>Check the repository URL and access token you configured before running this script.</p>';
            $markup[] = sprintf('<p><code>%s</code>', $e->getMessage());
            $markup[] = '</div>';

            echo $this->header() . implode("\n", $markup) . $this->footer();
        }
    }
}
