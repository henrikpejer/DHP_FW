<?php
declare(encoding="UTF8");
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2013-07-11 00:12
 */

namespace DHP\modules;

/**
 * Class Propel
 * @package DHP\modules
 *
 * We want this to be a fairly simple interface to a Propel store.
 *
 * We want to be able to let certain uri's be handled by this module.
 *
 * URIs
 * The uris must follow a certain pattern:
 *
 * model/:id
 * Model is the Propel to use, for instance, if we have an author model
 * and we want the post with id 4, the uri should be:
 *
 * author/4
 *
 * IF we have a model using the slug-behaviour, we could use the following
 *
 * author/Joseph-Heller
 *
 * model/page/:pageNumber
 * Here we have a pager query, meaning we want a page of posts from the model.
 * So if we have a author-model, the URI should be
 *
 * author/page/1
 *
 * Example URIs and how they will be interpreted as:
 *
 * author      => author/page/1
 * author/1    => Getting author with id = 1
 * author/page => getting the author with slug 'page'
 *
 *
 * METHODS
 * Different http methods either reads, creates, deletes or updates posts (CRUD!)
 *
 * GET    = read, returns the post and does not change the post
 * PUT    = updates an existing post
 * POST   = creates a new post, will return 201 along with a location to where the
 *          newly created post resides. IF slug-behaviour have been used, the slug
 *          will be used instead of the id ( author/Joseph-Heller)
 * DELETE = Will delete the post found
 *
 *
 * SORTS, GROUPS
 *
 * These kind of commands might be available further down the road. How they will be
 * implemented, have not been considered just yet.
 */
class Propel {

}