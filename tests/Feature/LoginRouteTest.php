<?php

test('the home page loads without generating a login url error', function () {
    $this->get('/')
        ->assertOk();
});

test('the login route can be generated without a role', function () {
    expect(route('login'))->toEndWith('/login');
});

test('visiting login without a role returns to the home page', function () {
    $this->get('/login')
        ->assertRedirect('/');
});

test('role login pages still load', function () {
    $this->get('/login/student')->assertOk();
    $this->get('/login/administrator')->assertOk();
});

test('guests are redirected home instead of a broken login route', function () {
    $this->get('/admin')
        ->assertRedirect('/');
});
