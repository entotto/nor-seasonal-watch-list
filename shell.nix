let
  pkgs = import (builtins.fetchTarball {
    # Descriptive name to make the store path easier to identify
    name = "nixpkgs-playwright-149";
    # Commit hash for nixos-unstable as of 2018-09-12
    url = "https://github.com/kalekseev/nixpkgs/archive/3d6bd425985b55c33bc58e41fa7f481468c1dffb.tar.gz";
    # Hash obtained using `nix-prefetch-url --unpack <url>`
    sha256 = "08mqqi1nrci42373c8fnwklijnalrn6xl3va3qcy8iw9q0kwzm9x";
  }) {};
in
pkgs.mkShell {
  buildInputs = [
    pkgs.php83
    pkgs.php83Packages.composer
    pkgs.symfony-cli
    pkgs.mariadb
    pkgs.playwright-driver.browsers
  ];
  shellHook = ''
    export PLAYWRIGHT_BROWSERS_PATH=${pkgs.playwright-driver.browsers}
    export PLAYWRIGHT_SKIP_VALIDATE_HOST_REQUIREMENTS=true
  '';
}
