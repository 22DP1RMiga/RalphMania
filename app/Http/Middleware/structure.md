```
RalphMania/
├── routes/
│   ├── api.php
│   ├── auth.php
│   ├── console.php
│   └── web.php
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── ProductController.php
│   │   │   ├── ContentController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── CartController.php
│   │   │   ├── OrderController.php
│   │   │   ├── ReviewController.php
│   │   │   ├── CommentController.php
│   │   │   └── ContactController.php
│   │   │
│   │   ├── Middleware/
│   │   │   ├── CheckRole.php
│   │   │   └── HandleIntertiaRequests.php
│   │
│   └── Models/
│       ├── User.php
│       ├── Role.php
│       ├── Product.php
│       ├── Category.php
│       ├── Content.php
│       ├── CartItem.php
│       ├── Order.php
│       ├── OrderItem.php
│       ├── Payment.php
│       ├── Review.php
│       ├── Comment.php
│       └── ContactMessage.php
│
├── resources/
│   ├── css/
│   │   └── app.css
│   │
│   ├── js/
│   │   ├── Components/
│   │   │   ├── ApplicationLogo.vue
│   │   │   ├── Checkbox.vue
│   │   │   ├── DangerButton.vue
│   │   │   ├── Dropdown.vue
│   │   │   ├── DropdownLink.vue
│   │   │   ├── InputError.vue
│   │   │   ├── InputLabel.vue
│   │   │   ├── Modal.vue
│   │   │   ├── NavLink.vue
│   │   │   ├── PrimaryButton.vue
│   │   │   ├── ResponsiveNavLink.vue
│   │   │   ├── SecondaryButton.vue
│   │   │   └── TextInput.vue
│   │   │
│   │   ├── Layouts/
│   │   │   ├── AuthenticatedLayout.vue
│   │   │   └── GuestLayout.vue
│   │   │
│   │   ├── Pages/
│   │   │   ├── Auth/
│   │   │   │   ├── ConfirmPassword.vue
│   │   │   │   ├── ForgotPassword.vue
│   │   │   │   ├── Login.vue
│   │   │   │   ├── Register.vue
│   │   │   │   ├── ResetPassword.vue
│   │   │   │   └── VerifyEmail.vue
│   │   │   │
│   │   │   ├── Profile/
│   │   │   │   ├── Partials/
│   │   │   │   │   ├── DeleteUserNorm.vue
│   │   │   │   │   ├── UpdatePasswordForm.vue
│   │   │   │   │   └── UpdateProfileInformationForm.vue
│   │   │   │   │
│   │   │   │   └── Edit.vue
│   │   │   │
│   │   │   ├── Dashboard.vue
│   │   │   └── Welcome.vue
│   │   │
│   │   ├── app.js
│   │   └── bootstrap.js
│   │
│   └── views/
│       └── app.blade.php
│
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── services.php
│   └── session.php
│
└── .env
```
