class LiveChannel {
  final String id;
  final String title;
  final String? logoUrl;
  final String streamUrl;
  final String? category;
  final bool isDrm;
  final String? drmLicenseUrl;

  LiveChannel({
    required this.id,
    required this.title,
    this.logoUrl,
    required this.streamUrl,
    this.category,
    this.isDrm = false,
    this.drmLicenseUrl,
  });

  factory LiveChannel.fromJson(Map<String, dynamic> json) {
    return LiveChannel(
      id: json['id'],
      title: json['title'],
      logoUrl: json['logoUrl'],
      streamUrl: json['streamUrl'],
      category: json['category'],
      isDrm: json['isDrm'] ?? false,
      drmLicenseUrl: json['drmLicenseUrl'],
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'title': title,
    'logoUrl': logoUrl,
    'streamUrl': streamUrl,
    'category': category,
    'isDrm': isDrm,
    'drmLicenseUrl': drmLicenseUrl,
  };
}
