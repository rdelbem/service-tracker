## Service Tracker STOMLC
This repository houses a WordPress plugin. If you're searching for a manual or guidance on how to use the Service Tracker STOLMC, please visit this [site](https://delbem.net/portfolio/service-tracker-sto/).

## For developers and WordPress plugin reviewers
Developers who wish to contribute are encouraged to fork this repo and submit a pull request. **The sections below provide clarity on this plugin's development flow.**

### 1. Stable plugin generation
- **Automated Stable Version Generation**: This repository features a GitHub workflow that auto-generates a stable branch based on the latest main branch. Whenever code is merged into the main branch, the stable version is updated accordingly.

- **Manual Stable Version Generation**: Alternatively, you can clone this repo, run `npm install`, `composer install`, and then `npm run generate:stable`. This will produce a stable version derived from your current working branch. It will be saved as a zip file in the directory above.

### 2. WordPress code reviewers
Please note that this repo serves as the development version of what we intend to offer to WordPress users. The end-users receive a stable, compiled version without any development-related files or folders. **Given this, it's advisable to review both this development repository, which includes human-readable JS, and the stable version.**

## License

This WordPress plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the [GNU General Public License](http://www.gnu.org/licenses/gpl-2.0.html) for more details.

