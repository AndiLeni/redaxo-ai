<?php

$addon = rex_addon::get('ai');

$form = rex_config_form::factory("ai");

$field = $form->addSelectField('category');
$catSelect = new rex_media_category_select();
$field->setSelect($catSelect);
$field->setLabel("Medien Kategorie zur Speicherung");


$field = $form->addTextField('openaiKey');
$field->setLabel("OpenAI API Key");

$field = $form->addTextField('gcpKey');
$field->setLabel("Google Cloud API Key");


$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', "Einstellungen", false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');
