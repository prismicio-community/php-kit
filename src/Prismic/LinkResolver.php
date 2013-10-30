<?php

/*
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic;

abstract class LinkResolver {

  public abstract function resolve($link);

  public function resolveDocument($document) {
    return $this->resolve($this->asLink($document));
  }

  public function resolveLink($link) {
    return $this->resolve($link);
  }

  public function isBookmarkDocument($api, $document, $bookmark) {
    return $this->isBookmark($api, $this->asLink($document), $bookmark);
  }

  public function isBookmark($api, $link, $bookmark) {
    $maybeId = $api->bookmark($bookmark);
    if ($maybeId == $link->getId()) {
      return true;
    }
    return false;
  }

  private function asLink($document) {
    return new Fragment\Link\DocumentLink($document->getId(), $document->getType(), $document->getTags(), $document->getSlug(), false);
  }

}
