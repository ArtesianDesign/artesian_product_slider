<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductVariation\ProductVariation as StoreProductVariation;

if ($products) { ?>

    <div class="flexslider product-slider--wrapper">

        <ul class="slides product-slider" id="product-slider-<?=$bID?>">

        <?php
        $i = 1;

        foreach ($products as $product) {
            $optionGroups = $product->getProductOptionGroups();
            $optionItems = $product->getProductOptionItems(true);

            if ($product->hasVariations()) {
                $variations = StoreProductVariation::getVariationsForProduct($product);
                $variationLookup = array();
                if (!empty($variations)) {
                    foreach ($variations as $variation) {
                        // returned pre-sorted
                        $ids = $variation->getOptionItemIDs();
                        $variationLookup[implode('_', $ids)] = $variation;
                    }
                }
            }

            //this is done so we can get a type of active class if there's a product list on the product page
            $class = "product-slider--item";
            if (Page::getCurrentPage()->getCollectionID()==$product->getProductPageID()) {
                $class .= " on-product-page";
            }
            ?>

            <li class="<?php echo $class?>">

                <div class="product-slider-item--inner product-id-<?php echo $product->getProductID()?>">

                    <?php
                    $imgObj = $product->getProductImageObj();

                    if (is_object($imgObj)) {
                        $thumb = $ih->getThumbnail($imgObj, 800, 533.33, true); ?>
                        <div class="product-slider--image">
                            <img src="<?php echo $thumb->src?>" class="img-responsive">
                        </div>
                    <?php
                    } // if is_obj($imgObj) ?>

                    <div class="product-slider--details">

                        <?php if ($showQuickViewLink) { ?>
                        <a class="product-quick-view" href="javascript:vividStore.productModal(<?php echo $product->getProductID()?>);">
                            <?php echo t("Quick View")?>
                        </a>
                        <?php } ?>

                        <h3 class="product-slider--name"><?php echo $product->getProductName()?></h3>
                        <span class="product-slider--price">
                        <?php
                            $salePrice = $product->getProductSalePrice();
                            if (isset($salePrice) && $salePrice != "") {
                                echo '<span class="sale-price">'.$product->getFormattedSalePrice().'</span>';
                                echo '<span class="original-price">'.$product->getFormattedOriginalPrice().'</span>';
                            } else {
                                echo $product->getFormattedPrice();
                            }
                        ?>
                        </span>

                        <?php if ($showDescription) { ?>
                        <div class="product-slider--description"><?php echo $product->getProductDesc()?></div>
                        <?php } ?>

                        <?php if ($showPageLink && !$showAddToCart) { ?>
                            <a href="<?php echo URL::page(Page::getByID($product->getProductPageID()))?>" class="btn btn-default btn-sm btn-more-details"><?php echo t("More Details")?></a>
                        <?php } ?>

                        <?php if ($showAddToCart) {
                            echo '<div class="product-option-groups">';
                            foreach ($optionGroups as $optionGroup) {
                                $groupoptions = array();
                                foreach ($optionItems as $option) {
                                    if ($option->getProductOptionGroupID() == $optionGroup->getID()) {
                                        $groupoptions[] = $option;
                                    }
                                }
                                if (!empty($groupoptions)) { ?>

                                <div class="product-option-group">
                                    <label class="option-group-label"><?php echo $optionGroup->getName() ?></label>
                                    <select name="pog<?php echo $optionGroup->getID() ?>">
                                        <?php foreach ($groupoptions as $option) { ?>
                                            <option value="<?php echo $option->getID() ?>"><?php echo $option->getName() ?></option>
                                        <?php  } ?>
                                    </select>
                                </div>
                            <?php
                                 } // if $group options
                            }
                            echo '</div>';

                            if ($showPageLink) { ?>
                                <a href="<?php echo URL::page(Page::getByID($product->getProductPageID()))?>" class="btn btn-default btn-sm btn-more-details"><?php echo t("More Details")?></a>
                            <?php } ?>

                            <?php if($product->isSellable()) {?>
                                <a href="javascript:vividStore.addToCart(<?php echo $product->getProductID()?>,'list')" class="btn btn-primary btn-add-to-cart">
                                    <?php echo($btnText ? h($btnText) : t("Add to Cart"))?>
                                </a>
                            <?php } else { ?>
                            <span class="out-of-stock-label"><?php echo t("Out of Stock")?></span>
                                <?php } ?>
                        <?php } //if showAddToCart ?>
                        </div><!-- .product-slider--details -->

                </div><!-- .product-slider-item--inner -->

            </li><!-- .product-slider--item -->


            <?php if ($product->hasVariations() && !empty($variationLookup)) { ?>
                <script>
                    $(function() {
                        <?php
                            $variationData = array();
                            foreach ($variationLookup as $key=>$variation) {
                                $product->setVariation($variation);

                                $imgObj = $variation->getVariationImageObj();

                                if ($imgObj) {
                                    $thumb = Core::make('helper/image')->getThumbnail($imgObj, 800, 533.33, true);
                                }

                                $variationData[$key] = array(
                                    'price'=>$product->getFormattedOriginalPrice(),
                                    'saleprice'=>$product->getFormattedSalePrice(),
                                    'available'=>($variation->isSellable()),
                                    'imageThumb'=>$thumb ? $thumb->src : '',
                                    'image'=>$imgObj ? $imgObj->getRelativePath() : '');
                            }
                        ?>


                        $('.product-slider #form-add-to-cart-list-<?php echo $product->getProductID()?> select').change(function(){
                            var variationdata = <?=json_encode($variationData)?>;
                            var ar = [];
                            $('.product-slider #form-add-to-cart-list-<?php echo $product->getProductID()?> select').each(function(){
                                ar.push($(this).val());
                            });

                            ar.sort();

                            var pli = $(this).closest('.product-slider--item-inner');

                            if (variationdata[ar.join('_')]['saleprice']) {
                                var pricing =  '<span class="sale-price">'+ variationdata[ar.join('_')]['saleprice']+'</span>' +
                                    '<span class="original-price">' + variationdata[ar.join('_')]['price'] +'</span>';

                                pli.find('.product-slider--price').html(pricing);
                            } else {
                                pli.find('.product-slider--price').html(variationdata[ar.join('_')]['price']);
                            }
                            if (variationdata[ar.join('_')]['available']) {
                                pli.find('.out-of-stock-label').addClass('hidden');
                                pli.find('.btn-add-to-cart').removeClass('hidden');
                            } else {
                                pli.find('.out-of-stock-label').removeClass('hidden');
                                pli.find('.btn-add-to-cart').addClass('hidden');
                            }
                            if (variationdata[ar.join('_')]['imageThumb']) {
                                var image = pli.find('.product-slider--imgage img');
                                if (image) {
                                    image.attr('src', variationdata[ar.join('_')]['imageThumb']);
                                }
                            }

                        });
                    });
                </script>
            <?php } ?>

            <?php
                // if ($i%$productsPerRow==0) {
                //     echo "</li>";
                //     echo "<li class='slide product-item'>";
                //     //this helps to keep rows straight (products from floating under smaller height products).
                // }
            $i++;
        }// foreach
        ?>
        </ul><!-- /.product-slider -->

        <?php
        if ($showPagination) {
            if ($paginator->getTotalPages() > 1) {
                echo $pagination;
            }
        } ?>
    </div><!-- /.flexslider -->

    <script>
    $(function(){
        $('.flexslider.product-slider--wrapper').flexslider({
            'smoothHeight'   : true,
            'animationSpeed' : 500,
            'slideshowSpeed' : 4000
        });
    });
    </script>

    <?php
} else { ?>
    <div class="alert alert-info"><?php echo t("No Products Available")?></div>
<?php } ?>
