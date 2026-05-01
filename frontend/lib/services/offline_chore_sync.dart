import 'package:hive/hive.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class OfflineChoreSync {
  static const String choreBoxName = 'chores';
  static const String pendingSyncBoxName = 'pending_sync';

  Future<void> init() async {
    await Hive.openBox(choreBoxName);
    await Hive.openBox(pendingSyncBoxName);
  }

  Future<void> completeChore(int choreId) async {
    final choreBox = Hive.box(choreBoxName);
    final pendingSyncBox = Hive.box(pendingSyncBoxName);

    // Update local state
    final chore = choreBox.get(choreId);
    if (chore != null) {
      chore['status'] = 'completed';
      await choreBox.put(choreId, chore);
    }

    // Add to pending sync queue
    await pendingSyncBox.add({
      'chore_id': choreId,
      'completed_at': DateTime.now().toIso8601String(),
    });

    // Try to sync immediately
    syncPendingChores();
  }

  static const String baseUrl = String.fromEnvironment('API_BASE_URL', defaultValue: 'https://api.chorequest.com/v1');

  Future<void> syncPendingChores() async {
    final pendingSyncBox = Hive.box(pendingSyncBoxName);

    while (pendingSyncBox.isNotEmpty) {
      final syncData = pendingSyncBox.getAt(0);
      try {
        final response = await http.post(
          Uri.parse('$baseUrl/chores/complete'),
          headers: {'Content-Type': 'application/json'},
          body: jsonEncode(syncData),
        );

        if (response.statusCode == 200) {
          await pendingSyncBox.deleteAt(0);
        } else {
          // Server error, stop and retry later
          break;
        }
      } catch (e) {
        // Offline or server down, will retry later
        break;
      }
    }
  }

  List getAllChores() {
    return Hive.box(choreBoxName).values.toList();
  }
}
