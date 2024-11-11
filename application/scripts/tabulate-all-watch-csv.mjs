import { promises as fs, createReadStream } from 'node:fs';
import { parse } from 'csv-parse';
import { stringify } from 'csv-stringify/sync';
import { finished } from 'stream/promises';

async function parseCSV(csvPath) {
    const results = [];
    const stream = createReadStream(csvPath)
      .pipe(parse())
      .on('data', (data) => results.push(data));
    await finished(stream);
    return results;
}

const moodEmojis = [
    ['heart-eyes', 5],
    ['smile', 1],
    ['neutral', -1],
    ['frown', -5],
];

function getMoodEmoji(moodAverage) {
    for (const [emoji, moodAverageThreshold] of moodEmojis) {
        if (moodAverage > moodAverageThreshold) {
            return emoji;
        }
    }
    return moodEmojis[moodEmojis.length - 1][0];
}

async function main(csvPath) {
    const rows = await parseCSV(csvPath);
    const activityCounts = {};
    const scoreCounts = {};
    const moodCounts = {};
    const startOfSummaryColumns = rows[0].indexOf("PTW");
    const totalCountColumn = rows[0].indexOf("Total count");
    const calculatedRecColumn = rows[0].indexOf("Calculated rec");
    rows.forEach((row, rowIndex) => {
        if (rowIndex === 0) {
            // header row
            return;
        }
        const moodAverage = row[totalCountColumn] > 0 ? row[calculatedRecColumn] / row[totalCountColumn] : 0;
        const moodEmoji = getMoodEmoji(moodAverage);
        moodCounts[moodEmoji] = (moodCounts[moodEmoji] || 0) + 1;
        row.forEach((value, index) => {
            if (index === 0) {
                // title column
                return;
            }
            if (index >= startOfSummaryColumns) {
                return;
            }
            const isActivity = index % 2 === 1;
            if (value === '(No opinion)') {
                value = '';
            }
            if (isActivity) {
                const moodActivityCounts = activityCounts[moodEmoji] || {};
                moodActivityCounts[value] = (moodActivityCounts[value] || 0) + 1;
                activityCounts[moodEmoji] = moodActivityCounts;
            } else {
                const moodScoreCounts = scoreCounts[moodEmoji] || {};
                moodScoreCounts[value] = (moodScoreCounts[value] || 0) + 1;
                scoreCounts[moodEmoji] = moodScoreCounts;
            }
        })
    })
    const activityChoices = ['', 'Stopped', 'Watching', 'PTW'];
    const scoreChoices = ['', 'Unfavorable', 'Neutral', 'Favorable', 'Highly favorable', 'Th8a should cover'];
    // mood -> { activitySamples, scoreSamples }
    // totalSamples
    console.log(JSON.stringify(activityChoices));
    for (const [moodEmoji, _] of moodEmojis) {
        console.log(moodEmoji);
        console.log(JSON.stringify(activityChoices.map(activity => (activityCounts[moodEmoji] || {})[activity])));
    }
    console.log(JSON.stringify(scoreChoices));
    for (const [moodEmoji, _] of moodEmojis) {
        console.log(moodEmoji);
        console.log(JSON.stringify(scoreChoices.map(score => (scoreCounts[moodEmoji] || {})[score])));
    }
    console.log(JSON.stringify(moodCounts));
}

main(process.argv[2]);
