VTwin
=====

VTwin was created to make form validation easier to maintain by using a shared set of rules for frontside and serverside parsing. If you need to change rules, you do it one place without having to recode the front and back.

## Requirements
* jQuery 1.8+
* PHP 5.2+

## Notes
A modified version of validate.js is included in this repo and it is required for VTwin to work. You cannot use a vanilla version of validate.js with VTwin...mabye in a future update.

The PHP side of VTwin is basically a ported version of validate.js, but as a whole VTwin is designed for pretty basic validation (required, alphanuermic, email, telephone, etc). You can create custom server-side checks and hook those into your validation rules.
