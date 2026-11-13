# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {YOUR_AUTH_KEY}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Connectez-vous via `POST /api/login` avec vos identifiants pour obtenir un token Sanctum. Incluez ce token dans l'en-tête `Authorization: Bearer {token}` pour les endpoints protégés.
