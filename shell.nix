{ pkgs ? import <nixpkgs> {}
}:
pkgs.mkShell {
  buildInputs = [
    pkgs.php83
    pkgs.php83Packages.composer
    pkgs.symfony-cli
    pkgs.mariadb
  ];
}
