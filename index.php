<?php
/**
 * IntLegis Root Entry Point
 * 
 * This file allows the application to be served from the root directory
 * even when the actual entry point is in the /public folder.
 * This is useful for environments like shared hosting where you 
 * cannot easily point the document root to the /public folder.
 */

require __DIR__ . '/public/index.php';

