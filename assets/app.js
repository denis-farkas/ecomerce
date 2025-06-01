import { registerReactControllerComponents } from "@symfony/ux-react";

import "./bootstrap";
import "bootstrap/dist/css/bootstrap.min.css";
import "./styles/app.css";
// Import Bootstrap JavaScript
import "@popperjs/core";
import "bootstrap"; // Add this line

/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */

registerReactControllerComponents(
  require.context("./react/controllers", true, /\.(j|t)sx?$/)
);
