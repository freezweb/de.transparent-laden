class User {
  final int id;
  final String email;
  final String? firstName;
  final String? lastName;
  final String? street;
  final String? city;
  final String? postalCode;
  final String? country;
  final String status;

  const User({
    required this.id,
    required this.email,
    this.firstName,
    this.lastName,
    this.street,
    this.city,
    this.postalCode,
    this.country,
    required this.status,
  });

  factory User.fromJson(Map<String, dynamic> json) => User(
        id: json['id'] as int,
        email: json['email'] as String,
        firstName: json['first_name'] as String?,
        lastName: json['last_name'] as String?,
        street: json['street'] as String?,
        city: json['city'] as String?,
        postalCode: json['postal_code'] as String?,
        country: json['country'] as String?,
        status: json['status'] as String? ?? 'active',
      );

  String get displayName {
    if (firstName != null && lastName != null) return '$firstName $lastName';
    if (firstName != null) return firstName!;
    return email;
  }
}

class AuthState {
  final User? user;
  final String? accessToken;
  final bool isAuthenticated;

  const AuthState({this.user, this.accessToken, this.isAuthenticated = false});

  const AuthState.initial() : user = null, accessToken = null, isAuthenticated = false;
}
