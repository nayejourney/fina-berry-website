<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function test_rupiah_memakai_pemisah_titik(): void
    {
        $this->assertSame('Rp 15.000', rupiah(15000));
        $this->assertSame('Rp 0', rupiah(null));
    }

    public function test_wa_number_dinormalisasi_ke_format_internasional(): void
    {
        $this->assertSame('6281234567890', wa_number('0812-3456-7890'));
        $this->assertSame('6281234567890', wa_number('+62 812 3456 7890'));
        $this->assertSame('6281234567890', wa_number('81234567890'));
        $this->assertSame('', wa_number(''));
    }
}
