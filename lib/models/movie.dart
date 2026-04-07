class Movie {
  final String id;
  final String title;
  final String? description;
  final String? posterUrl;
  final String? backdropUrl;
  final double? rating;
  final String? releaseDate;
  final List<String>? genres;
  final List<StreamSource>? sources;

  Movie({
    required this.id,
    required this.title,
    this.description,
    this.posterUrl,
    this.backdropUrl,
    this.rating,
    this.releaseDate,
    this.genres,
    this.sources,
  });

  factory Movie.fromJson(Map<String, dynamic> json) {
    return Movie(
      id: json['id'],
      title: json['title'],
      description: json['description'],
      posterUrl: json['posterUrl'],
      backdropUrl: json['backdropUrl'],
      rating: json['rating']?.toDouble(),
      releaseDate: json['releaseDate'],
      genres: json['genres'] != null ? List<String>.from(json['genres']) : null,
      sources: json['sources'] != null ? (json['sources'] as List).map((i) => StreamSource.fromJson(i)).toList() : null,
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'title': title,
    'description': description,
    'posterUrl': posterUrl,
    'backdropUrl': backdropUrl,
    'rating': rating,
    'releaseDate': releaseDate,
    'genres': genres,
    'sources': sources?.map((i) => i.toJson()).toList(),
  };
}

class StreamSource {
  final String url;
  final String quality;
  final String? platform; // e.g., 'Google Drive', 'Mega', 'Embed'
  final bool isDrm;
  final String? drmLicenseUrl;

  StreamSource({
    required this.url,
    required this.quality,
    this.platform,
    this.isDrm = false,
    this.drmLicenseUrl,
  });

  factory StreamSource.fromJson(Map<String, dynamic> json) {
    return StreamSource(
      url: json['url'],
      quality: json['quality'],
      platform: json['platform'],
      isDrm: json['isDrm'] ?? false,
      drmLicenseUrl: json['drmLicenseUrl'],
    );
  }

  Map<String, dynamic> toJson() => {
    'url': url,
    'quality': quality,
    'platform': platform,
    'isDrm': isDrm,
    'drmLicenseUrl': drmLicenseUrl,
  };
}
