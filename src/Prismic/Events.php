<?php

namespace Prismic;

final class Events
{

    /**
     * The prismic.pre_submit event is dispatched just before a search form is
     * submitted.
     *
     * Event listeners receive a Prismic\Event\PreSubmitEvent instance.
     *
     * @var string
     */
    const PRE_SUBMIT = 'prismic.pre_submit';

    /**
     * The prismic.post_submit event is dispatched just after a search form
     * submission's response is received.
     *
     * Event listeners receive a Prismic\Event\PostSubmitEvent instance.
     *
     * @var string
     */
    const POST_SUBMIT = 'prismic.post_submit';

}
