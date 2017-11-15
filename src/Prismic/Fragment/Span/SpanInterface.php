<?php
/**
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment\Span;

/**
 * This interface embodies any span (emphase, strong, link, ...).
 * A span comes in a array of spans, which is served with a raw text. If the raw text is
 * "Hello world!", and the span's start is 6 and its end is 11, then the piece that
 * is meant to be the span is "world".
 *
 * The known implementations are EmSpan, StrongSpan and HyperlinkSpan.
 */
interface SpanInterface
{
}
