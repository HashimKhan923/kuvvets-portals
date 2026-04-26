<?php
namespace App\View\Components\Emails;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Layout extends Component
{
    public function __construct(
        public string $subject = 'KUVVET Notification'
    ) {}

    public function render(): View|Closure|string
    {
        return view('emails.layout');
    }
}