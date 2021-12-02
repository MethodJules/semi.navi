<?php 

namespace Drupal\news\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
/**
 * Provides a Block for register Newsletter
 * 
 * @Block(
 *   id = "news_newsletter_cta_block",
 *   admin_label = @Translation("Newsletter CTA Block"),
 * )
 */

 class NewsBlock extends BlockBase {

 public function build() { 
    $link = \Drupal::service('link_generator')->generateFromLink(Link::createFromRoute($this->t('Newsletter abbonieren'),'news.newsletter_order' ));

     return ['#markup' => $link];
 }

 }

