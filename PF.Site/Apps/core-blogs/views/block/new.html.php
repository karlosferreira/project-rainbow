<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if !count($aBlogs)}
    <div class="help-block">
        {_p var='no_blogs_have_been_added_yet'}
        <ul class="action">
            <li><a href="{url link='blog.add'}">{_p var='be_the_first_to_add_a_blog'}</a></li>
        </ul>
    </div>
{else}
    <div class="item-container with-blog">
        {foreach from=$aBlogs item=aItem}
            <div class="blog-item">
                <div class="item-outer">
                    {if !empty($aItem.image)}
                        <!-- image -->
                        <a class="item-media-src" href="{permalink module='blog' id=$aItem.blog_id title=$aItem.title}">
                            <span style="background-image: url({$aItem.image})"></span>
                        </a>
                    {/if}
                    <div class="item-inner">
                        <!-- title -->
                        <div class="item-title">
                            <a href="{permalink module='blog' id=$aItem.blog_id title=$aItem.title}" title="{$aItem.title|clean}">{$aItem.title|clean}</a>
                        </div>
                        <!-- author -->
                        <div class="item-author dot-separate">
                            <span>{_p var='by_full_name' full_name=$aItem|user:'':'':50:'':'author'}</span>
                        </div>
                        <div class="total-view">
                            <span>
                                {$aItem.time_stamp|convert_time:'core.global_update_time'}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        {/foreach}
    </div>
{/if}
