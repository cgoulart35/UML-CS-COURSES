package com.example.databasephase3android;

import android.net.SSLCertificateSocketFactory;
import android.os.StrictMode;
import android.util.Log;

import org.apache.http.conn.ssl.AllowAllHostnameVerifier;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.DataOutputStream;
import java.io.InputStreamReader;
import java.net.URL;

import javax.net.ssl.HttpsURLConnection;

public class UtilityClass {

    public static JSONArray makePOST(String query) {
        try {
            if (android.os.Build.VERSION.SDK_INT > 9) {
                StrictMode.ThreadPolicy policy = new
                        StrictMode.ThreadPolicy.Builder().permitAll().build();
                StrictMode.setThreadPolicy(policy);
            }

            //localhost will NOT work for database because localhost is the phone/emulator
            //use 10.0.2.2 for the computer the emulator is running on, or another IP address

            URL url = new URL("https://10.0.2.2/db2/queryAPI.php");
            HttpsURLConnection connection = (HttpsURLConnection) url.openConnection();
            connection.setSSLSocketFactory(SSLCertificateSocketFactory.getInsecure(0, null));
            connection.setHostnameVerifier(new AllowAllHostnameVerifier());
            connection.setRequestMethod("POST");
            connection.setRequestProperty("Content-Type", "application/json;charset=UTF-8");
            connection.setRequestProperty("Accept", "application/json");
            connection.setDoOutput(true);
            connection.setDoInput(true);

            JSONObject jsonParam = new JSONObject();
            jsonParam.put("query", query);

            Log.i("JSON", jsonParam.toString());
            DataOutputStream os = new DataOutputStream(connection.getOutputStream());
            os.writeBytes(jsonParam.toString());

            os.flush();
            os.close();

            BufferedReader br = new BufferedReader(new InputStreamReader(connection.getInputStream()));
            StringBuilder sb = new StringBuilder();
            String line;
            while ((line = br.readLine()) != null) {
                sb.append(line+"\n");
            }
            br.close();
            String response = sb.toString();

            Log.d("STATUS", String.valueOf(connection.getResponseCode()));
            Log.d("MSG" , connection.getResponseMessage());
            Log.d("RESPONSE" , response);

            connection.disconnect();

            try {
                JSONArray jsonResult = new JSONArray(response);
                return jsonResult;
            }catch (JSONException err){
                Log.d("JSON Error!", err.toString());
            }
        }
        catch(Exception exception) {
            exception.printStackTrace();
        }
        return null;
    }

    public static boolean isUserAParent(int id) {
        //figure out with queries if student, parent, or admin
        JSONArray isParentQueryResult = UtilityClass.makePOST(String.format("SELECT * FROM parents WHERE parent_id = %d", id));
        if (isParentQueryResult != null && isParentQueryResult.length() > 0) {
            return true;
        }
        return false;
    }

    public static boolean isUserAStudent(int id) {
        JSONArray isStudentQueryResult = UtilityClass.makePOST(String.format("SELECT * FROM students WHERE student_id = %d", id));
        if (isStudentQueryResult != null && isStudentQueryResult.length() > 0) {
            return true;
        }
        return false;
    }

    public static boolean isUserAnAdmin(int id) {
        JSONArray isAdminQueryResult = UtilityClass.makePOST(String.format("SELECT * FROM admins WHERE admin_id = %d", id));
        if (isAdminQueryResult != null && isAdminQueryResult.length() > 0) {
            return true;
        }
        return false;
    }

    public static boolean isUserMentorOfMeeting(int userID, int meetingID) {
        JSONArray isUserMentorOfMeetingArray = UtilityClass.makePOST(String.format("SELECT * FROM users INNER JOIN enroll2 ON enroll2.mentor_id = users.id WHERE meet_id = %d", meetingID));
        for (int i = 0; i < isUserMentorOfMeetingArray.length(); i++) {
            try {
                JSONObject userObject = isUserMentorOfMeetingArray.getJSONObject(i);
                int mentorID = userObject.getInt("mentor_id");
                if (userID == mentorID) {
                    return true;
                }
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
        return false;
    }

    public static boolean isUserMenteeOfMeeting(int userID, int meetingID) {
        JSONArray isUserMenteeOfMeetingArray = UtilityClass.makePOST(String.format("SELECT * FROM users INNER JOIN enroll ON enroll.mentee_id = users.id WHERE meet_id = %d", meetingID));
        for (int i = 0; i < isUserMenteeOfMeetingArray.length(); i++) {
            try {
                JSONObject userObject = isUserMenteeOfMeetingArray.getJSONObject(i);
                int mentorID = userObject.getInt("mentee_id");
                if (userID == mentorID) {
                    return true;
                }
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
        return false;
    }

    public static boolean isUserParentOfMentorOfMeeting(int userID, int meetingID) {
        JSONArray isUserParentOfMentorOfMeetingArray = UtilityClass.makePOST(String.format("SELECT parent_id FROM students WHERE student_id IN (SELECT mentor_id FROM enroll2 WHERE meet_id = %d)", meetingID));
        for (int i = 0; i < isUserParentOfMentorOfMeetingArray.length(); i++) {
            try {
                JSONObject userObject = isUserParentOfMentorOfMeetingArray.getJSONObject(i);
                int parentID = userObject.getInt("parent_id");
                if (userID == parentID) {
                    return true;
                }
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
        return false;
    }

    public static boolean isUserParentOfMenteeOfMeeting(int userID, int meetingID) {
        JSONArray isUserParentOfMenteeOfMeetingArray = UtilityClass.makePOST(String.format("SELECT parent_id FROM students WHERE student_id IN (SELECT mentee_id FROM enroll WHERE meet_id = %d)", meetingID));
        for (int i = 0; i < isUserParentOfMenteeOfMeetingArray.length(); i++) {
            try {
                JSONObject userObject = isUserParentOfMenteeOfMeetingArray.getJSONObject(i);
                int parentID = userObject.getInt("parent_id");
                if (userID == parentID) {
                    return true;
                }
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
        return false;
    }
}
