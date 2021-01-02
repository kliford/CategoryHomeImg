{if $categories|@count > 0}
<!-- Module Category Home Img -->
    <section class="containerModulCategoryHomeImg">
        <div class="row">
            <div class="col contentModulCategoryHomeImg">
                {foreach from=$categories item=category}
                    <div class="sectionCategory categoryWrapper id_category_{$category.id_category_home}">
                        <a href="/{$category.id_category_home}-{$category.link_rewrite}">
                            <img src="{$link->getMediaLink("`$smarty.const._MODULE_DIR_`categoryhomeimg/images/`$category.url_img|escape:'htmlall':'UTF-8'`")}" alt="{$category.name_category|escape:'htmlall':'UTF-8'}" />   
                            <div class='sectionTitleCategory'>
                                <h3>{$category.name_category}</h3>
                            </div>
                        </a>
                    </div>
                {/foreach}
            </div>
        </div>
    </section>
<!-- /Module Category Home Img -->
{/if}