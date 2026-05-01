import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

class OfflineSyncService {
  static const String _syncKey = 'pending_sync_actions';

  /// Queue an action (e.g., document signing, task completion) for later sync.
  static Future<void> queueAction(String type, Map<String, dynamic> data) async {
    final prefs = await SharedPreferences.getInstance();
    final List<String> pending = prefs.getStringList(_syncKey) ?? [];

    final action = {
      'type': type,
      'data': data,
      'timestamp': DateTime.now().toIso8601String(),
    };

    pending.add(jsonEncode(action));
    await prefs.setStringList(_syncKey, pending);
  }

  /// Get all pending actions.
  static Future<List<Map<String, dynamic>>> getPendingActions() async {
    final prefs = await SharedPreferences.getInstance();
    final List<String> pending = prefs.getStringList(_syncKey) ?? [];
    return pending.map((item) => jsonDecode(item) as Map<String, dynamic>).toList();
  }

  /// Clear synced actions.
  static Future<void> clearSyncedActions() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_syncKey);
  }
}
