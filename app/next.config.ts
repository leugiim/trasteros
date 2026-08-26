import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // Necesario para el build de Docker: empaqueta un server.js autocontenido
  // con solo las dependencias de producción que realmente usa el app router.
  output: "standalone",
};

export default nextConfig;
