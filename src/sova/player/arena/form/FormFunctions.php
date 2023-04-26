<?php
namespace sova\player\arena\form;

class FormFunctions{

    public function __construct(private string $icon){}

    public function getIcon(): string{
        return $this->icon;
    }
}