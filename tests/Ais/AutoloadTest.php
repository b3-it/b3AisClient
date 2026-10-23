<?php

namespace Ais\tests;

use Ais\Message\Message;
use Ais\Message\Message123;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

class AutoloadTest extends TestCase
{
    // Eigener Prozess, damit die Helper-Klasse nicht bereits durch andere Tests geladen ist
    #[RunInSeparateProcess]
    public function testMessageClassCanBeLoadedBeforeHelper()
    {
        $message = new Message123(1, 1700000000);

        $this->assertInstanceOf(Message::class, $message);
    }

}
