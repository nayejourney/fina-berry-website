<?php

namespace Tests\Feature;

class AccessControlTest extends FinaBerryTestCase
{
    public function test_halaman_admin_tanpa_login_dialihkan_ke_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get('/admin/pesanan')->assertRedirect(route('login'));
    }

    public function test_staf_tidak_boleh_membuka_laporan_dan_analitik(): void
    {
        $this->actingAs($this->user('staff'));

        $this->get('/admin/laporan')->assertForbidden();
        $this->get('/admin/analitik')->assertForbidden();
        $this->get('/admin/pengaturan')->assertForbidden();
        $this->get('/admin/pesanan')->assertOk();
    }

    public function test_pemilik_boleh_laporan_tetapi_tidak_analitik_laba_rugi(): void
    {
        $this->actingAs($this->user('owner'));

        $this->get('/admin/laporan')->assertOk();
        $this->get('/admin/analitik')->assertForbidden();
    }

    public function test_super_admin_boleh_melihat_laba_rugi_dan_analitik(): void
    {
        $this->actingAs($this->user('super_admin'));

        $this->get('/admin/analitik')->assertOk();
        $this->get('/admin/laporan')->assertOk();
    }

    public function test_login_gagal_dan_berhasil(): void
    {
        $this->user('owner');

        $this->post('/admin/login', ['email' => 'owner@test.local', 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->post('/admin/login', ['email' => 'owner@test.local', 'password' => 'Rahasia123'])->assertRedirect('/admin');
        $this->assertAuthenticated();
    }
}
