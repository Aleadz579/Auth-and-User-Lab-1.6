# Auth & User Lab

This is my Symfony security lab project.  
Goal: Build secure authentication features.

It’s for learning + proving I actually understand:
- CSRF
- rate limiting / throttling
- password reset tokens
- email verification
- audit logging

No fancy frontend.

---

## What it can do (right now)

- Register + login (Symfony Security)
- Password reset flow (token link + expiry)
- Email verification (6-digit code)
- Delete email again
- CSRF checks on sensitive endpoints
- Throttling / blocking after too many wrong attempts
- Security event logging (audit log style)

---

## Tech / Stack

- PHP 8.x
- Symfony (6/7)
- Doctrine ORM
- Twig
- Symfony Mailer
