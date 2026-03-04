// Wrapper for hosts that use require() to start the app (e.g. LiteSpeed lsnode).
// SvelteKit build is ESM with top-level await; require() cannot load it.
const path = require('path');
import(path.join(__dirname, 'build', 'index.js')).catch((err) => {
  console.error(err);
  process.exit(1);
});
