<?php

use Gregwar\DSD\Form;

/**
 * Tests des contraintes
 *
 * @author Grégoire Passault <g.passault@gmail.com>
 */
class ConstraintsTests extends \PHPUnit_Framework_TestCase
{
    /**
     * Test le rendu d'un champ requis et du test
     */
    public function testRequired()
    {
        $form = $this->getForm('required.html');
        $this->assertContains('required=', "$form");

        $this->assertAccept($form, array(
            'name' => 'jack'
        ));

        $this->assertRefuse($form, array(
            'name' => ''
        ));

        $this->assertAccept($form, array(
            'name' => '0'
        ));
    }

    /**
     * Test d'envoi d'un array sur une valeur simple
     */
    public function testArray()
    {
        $form = $this->getForm('required.html');

        $this->assertRefuse($form, array(
            'name' => array('xyz')
        ));
    }

    /**
     * Test le rendu d'un champ optionel et du test
     */
    public function testOptional()
    {
        $form = $this->getForm('optional.html');
        $this->assertNotContains('required=', "$form");

        $this->assertAccept($form, array(
            'name' => ''
        ));

        $this->assertAccept($form, array(
            'name' => 'Jack'
        ));
    }

    /**
     * Test la longueur maximale
     */
    public function testMaxLength()
    {
        $form = $this->getForm('maxlength.html');
        
        $this->assertContains('maxlength', "$form");

        $this->assertAccept($form, array(
            'nick' => str_repeat('x', 100)
        ));

        $this->assertRefuse($form, array(
            'nick' => str_repeat('x', 101)
        ));
    }

    /**
     * Test la longueur minimale
     */
    public function testMinLength()
    {
        $form = $this->getForm('minlength.html');
        
        $this->assertNotContains('minlength', "$form");

        $this->assertAccept($form, array(
            'nick' => str_repeat('x', 10)
        ));

        $this->assertRefuse($form, array(
            'nick' => str_repeat('x', 9)
        ));
    }

    /**
     * Test de regex=""
     */
    public function testRegex()
    {
        $form = $this->getForm('regex.html');

        $this->assertNotContains('regex', "$form");

        $this->assertAccept($form, array(
            'nick' => 'hello'
        ));

        $this->assertRefuse($form, array(
            'nick' => 'hm hm'
        ));
    }

    /**
     * Test de min="" et max=""
     */
    public function testMinMax()
    {
        $form = $this->getForm('minmax.html');

        $this->assertNotContains('min', "$form");
        $this->assertNotContains('max', "$form");

        $this->assertAccept($form, array(
            'num' => 7
        ));

        $this->assertRefuse($form, array(
            'num' => 3
        ));

        $this->assertRefuse($form, array(
            'num' => 13
        ));
    }

    /**
     * Test de contrainte custom
     */
    public function testCustomConstraint()
    {
        $form = $this->getForm('custom.html');

        $form->addConstraint('name', function($value) {
            if ($value[0] == 'J') {
                return 'Le nom ne doit pas commencer par J';
            }
        });

        $this->assertAccept($form, array(
            'name' => 'Paul'
        ));

        $this->assertRefuse($form, array(
            'name' => 'Jack'
        ));
    }

    /**
     * Test de contrainte custom
     */
    public function testCaptcha()
    {
        $form = $this->getForm('captcha.html');
        $html = "$form";

        $this->assertContains('<img', $html);
        $this->assertContains('code', $html);

        $this->assertAccept($form, array(
            'code' => $form->get('code')->getCaptchaValue()
        ));

        $form = $this->getForm('captcha.html');
        $html = "$form";

        $this->assertRefuse($form, array(
            'code' => 'xxx'
        ));
    }

    /**
     * Test de valeur postée n'étant pas dans un select
     */
    public function testSelectOut()
    {
        $form = $this->getForm('select.html');

        $this->assertAccept($form, array(
            'city' => 'la'
        ));

        $this->assertRefuse($form, array(
            'city' => 'xy'
        ));
    }

    /**
     * Test des multiples
     */
    public function testMultiple()
    {
        $form = $this->getForm('multiple.html');
        $html = "$form";

        $this->assertContains('<script', $html);
        $this->assertContains('<a', $html);

        $this->assertRefuse($form, array(
            'names' => ''
        ));

        $this->assertAccept($form, array(
            'names' => array('a', 'b')
        ));

        $this->assertRefuse($form, array(
            'names' => array(str_repeat('x', 25))
        ));

        $this->assertRefuse($form, array(
            'names' => array(array('a', 'b'))
        ));
    }

    /**
     * Test qu'on ne peut pas changer les readonly
     */
    public function testReadOnly()
    {
        $form = $this->getForm('readonly.html');
        $html = "$form";

        $this->assertContains('Jack', $html);
        $this->assertContains('selected=', $html);

        $this->assertAccept($form, array(
            'nom' => 'Jack',
            'color' => 'g'
        ));

        $this->assertRefuse($form, array(
            'nom' => 'Jack',
            'color' => 'y'
        ));
    }

    /**
     * Teste le reset
     */
    public function testReset()
    {
        $form = $this->getForm('reset.html');

        $this->assertEquals('Jack', $form->name);

        $this->assertAccept($form, array(
            'name' => 'Paul'
        ));

        $this->assertEquals('Paul', $form->name);
        $form->reset();
        $this->assertEquals('Jack', $form->name);
    }

    /**
     * Test qu'un formulaire accepte les données fournies
     */
    private function assertAccept($form, $data) {
        $_POST = $data;
        $_POST['csrf_token'] = $form->getToken();
        $this->assertTrue($form->posted());
        $this->assertEmpty($form->check());
    }

    /**
     * Test qu'un formulaire rejette les données fournies
     */
    private function assertRefuse($form, $data) {
        $_POST = $data;
        $_POST['csrf_token'] = $form->getToken();
        $this->assertTrue($form->posted());
        $this->assertNotEmpty($form->check());
    }

    private function getForm($file)
    {
        return new Form(__DIR__.'/files/form/'.$file);
    }
}
