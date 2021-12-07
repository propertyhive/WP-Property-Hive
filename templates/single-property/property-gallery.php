<?php
/**
 * Single Property Image Gallery
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $propertyhive, $property;
if ( isset($images) && is_array($images) && !empty($images) ) {
?>
<style>
    /* Three image containers (use 25% for four, and 50% for two, etc) */
    .gallery-column {
        position: relative;
        float: left;
        width: 33.33%;
        <?php
        if ( isset($settings['padding']) && !empty($settings['padding']) )
        {
            ?>
            padding: <?php echo $settings['padding']; ?>px;
            <?php
        }
        ?>
    }

    <?php
    if ( isset($settings['color']) && !empty($settings['color']) )
    {
        ?>
        .gallery-row {
            background-color: <?php echo $settings['color']; ?>;
        }
        <?php
    }
    ?>

    /* Clear floats after image containers */
    .gallery-row::after {
        content: "";
        clear: both;
        display: table;
    }

    .more-images {
        position: absolute;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5); /* Black see-through */
        height: 100%;
        width: 100%;
        opacity:1;
        font-size: 20px;
        text-align: center;
        padding-top: 30%;
    }

    .more-images a {
        color: #f1f1f1;
    }
</style>

<script>
    function openGallery()
    {
        jQuery('a#more-images-link').trigger('click');
    }
</script>

<?php
    $imageNumber = 0;
    for ($i = 0 ; $i < 2; $i++) {
        echo '<div class="gallery-row">';
        for ($j = 0 ; $j < 3; $j++) {
            echo '<div class="gallery-column">';
            if ( isset($images[$imageNumber]) )
            {
                $id_text = $imageNumber == 5 ? 'id="more-images-link"' : '';

                echo '<a ' . $id_text . ' href="' . $images[$imageNumber]['url'] . '" data-fancybox="gallery">';

                echo '<img title="' . $images[$imageNumber]['title'] . '" src="' . $images[$imageNumber]['url'] . '" style="width:100%"></a>';

                if ( $imageNumber == 5 )
                {
                    echo '<div class="more-images" onclick="openGallery(); return false;"><a>Show all ' . count($images) . ' images</a></div>';
                }
            }
            echo '</div>';
            $imageNumber++;
        }
        echo '</div>';
    }

    while ( count($images) > ($imageNumber) )
    {
        echo '<a href="' . $images[$imageNumber]['url'] . '" data-fancybox="gallery"></a>';
        ++$imageNumber;
    }

    // Code second layout, for one main photo
} ?>