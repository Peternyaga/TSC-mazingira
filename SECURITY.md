# Credential configuration

Secrets previously committed to this project must be revoked or rotated by their service administrators. Configure fresh TSC_API_KEY, TSC_SMS_API_KEY, TSC_PAYROLL_API_KEY and TSC_CONTACT_API_KEY values in the deployment environment; never commit them. Rebuild the Laravel configuration cache after updating deployment credentials. The external image-service credential found in a removed comment also requires revocation. No production credentials or services were changed by this source cleanup.

Client applications must be coordinated with API key rotation. Shared keys embedded in mobile applications are extractable and should be replaced with user-based authentication.
