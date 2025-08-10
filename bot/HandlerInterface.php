<?php

interface HandlerInterface
{
    public function handle(string $rawUpdate): void;
}
