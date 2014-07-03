<?php

use Tomahawk\Forms\Form;

use Tomahawk\Forms\Element\Search;

class SearchTest extends PHPUnit_Framework_TestCase
{
    public function testHidden()
    {
        $form = new Form();

        $form->add(new Search('search'));

        $html = $form->render('search', array('class' => 'input-field'));

        $this->assertEquals('<input type="search" name="search" class="input-field">', $html);
    }
}