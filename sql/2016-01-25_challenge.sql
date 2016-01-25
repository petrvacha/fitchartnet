CREATE VIEW
    user_challenge_performance_view AS
SELECT
    C.id as challenge_id,
    SUM(AL.value) value,
    AL.created_at,
    U.id user_id,
    U.username,
    CU.color,
    C.start_at,
    C.end_at
FROM
    activity_log AL
JOIN
    user U ON U.id = AL.user_id
JOIN
    challenge C ON C.activity_id = AL.activity_id AND
    C.end_at > AL.created_at AND
    C.start_at < AL.created_at AND
    AL.active = 1
JOIN
    challenge_user CU ON CU.user_id = U.id AND
    CU.challenge_id = C.id AND
    CU.active = 1
GROUP BY
    challenge_id,
    user_id,
    (UNIX_TIMESTAMP(AL.created_at) + 7200) DIV 28800
ORDER BY
    AL.created_at ASC