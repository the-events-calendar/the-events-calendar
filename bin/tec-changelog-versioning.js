/**
 * TEC "modified semver" versioning strategy for @stellarwp/changelogger.
 *
 * Ported from the-events-calendar/actions templates/bin/ModifiedSemverVersioning.php
 * (Automattic\Jetpack\Changelogger\VersioningPlugin implementation). Allows an optional
 * 4th "hotfix" component: x.y.z or x.y.z.hotfix.
 *
 * Faithfully preserves the source behavior, including that the hotfix component is never
 * reset or incremented by getNextVersion() itself - it always carries forward unchanged from
 * the version being bumped from. Hotfix bumps/resets are handled by the release-prepare-branch
 * workflow, which computes and passes an explicit version rather than relying on significance-based
 * inference.
 */

const VERSION_RE =
	/^(?<major>\d+)\.(?<minor>\d+)\.(?<patch>\d+)(?:\.(?<hotfix>\d+))?(?:-(?<prerelease>(?:[0-9a-zA-Z-]+)(?:\.(?:[0-9a-zA-Z-]+))*))?(?:\+(?<buildinfo>[0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/;

/**
 * @param {string} version
 * @return {{major:number,minor:number,patch:number,hotfix:?number,prerelease:?string,buildinfo:?string}}
 */
function parseVersion(version) {
	const m = VERSION_RE.exec(version);
	if (!m) {
		throw new Error(`Version number "${version}" is not in a recognized format.`);
	}
	const g = m.groups;
	return {
		major: parseInt(g.major, 10),
		minor: parseInt(g.minor, 10),
		patch: parseInt(g.patch, 10),
		hotfix: g.hotfix !== undefined ? parseInt(g.hotfix, 10) : null,
		prerelease: g.prerelease !== undefined ? g.prerelease : null,
		buildinfo: g.buildinfo !== undefined ? g.buildinfo : null,
	};
}

/**
 * @param {{major:number,minor:number,patch:number,hotfix:?number,prerelease:?string,buildinfo:?string}} info
 * @return {string}
 */
function normalizeVersion(info) {
	let ret = `${info.major}.${info.minor}.${info.patch}`;
	if (info.hotfix) {
		ret += `.${info.hotfix}`;
	}
	if (info.prerelease !== null && info.prerelease !== undefined) {
		ret += `-${info.prerelease}`;
	}
	if (info.buildinfo !== null && info.buildinfo !== undefined) {
		ret += `+${info.buildinfo}`;
	}
	return ret;
}

/**
 * @param {string} currentVersion
 * @param {"major"|"minor"|"patch"} significance
 * @return {string}
 */
function getNextVersion(currentVersion, significance) {
	const info = parseVersion(currentVersion);

	if (significance === 'major') {
		info.patch = 0;
		if (info.major === 0) {
			// eslint-disable-next-line no-console
			console.warn('Semver does not automatically move version 0.y.z to 1.0.0.');
			// eslint-disable-next-line no-console
			console.warn("You will have to do that manually when you're ready for the first release.");
			info.minor += 1;
		} else {
			info.minor = 0;
			info.major += 1;
		}
	} else if (significance === 'minor') {
		info.patch = 0;
		info.minor += 1;
	} else {
		info.patch += 1;
	}

	return normalizeVersion(info);
}

/**
 * @param {string} version
 * @return {boolean}
 */
function isValidVersion(version) {
	try {
		parseVersion(version);
		return true;
	} catch (e) {
		return false;
	}
}

/**
 * @param {string} a
 * @param {string} b
 * @return {number}
 */
function compareVersions(a, b) {
	const aa = parseVersion(a);
	const bb = parseVersion(b);

	if (aa.major !== bb.major) {
		return aa.major - bb.major;
	}
	if (aa.minor !== bb.minor) {
		return aa.minor - bb.minor;
	}
	if (aa.patch !== bb.patch) {
		return aa.patch - bb.patch;
	}

	if (aa.hotfix || bb.hotfix) {
		if (!aa.hotfix) {
			return -1 * bb.hotfix;
		}
		if (!bb.hotfix) {
			return aa.hotfix;
		}
		if (aa.hotfix !== bb.hotfix) {
			return aa.hotfix - bb.hotfix;
		}
	}

	if (aa.prerelease === null) {
		return bb.prerelease === null ? 0 : 1;
	}
	if (bb.prerelease === null) {
		return -1;
	}

	const aaa = aa.prerelease.split('.');
	const bbb = bb.prerelease.split('.');
	const al = aaa.length;
	const bl = bbb.length;
	for (let i = 0; i < al && i < bl; i++) {
		const ap = aaa[i];
		const bp = bbb[i];
		const aIsNum = /^\d+$/.test(ap);
		const bIsNum = /^\d+$/.test(bp);
		if (aIsNum && bIsNum) {
			if (parseInt(ap, 10) !== parseInt(bp, 10)) {
				return parseInt(ap, 10) - parseInt(bp, 10);
			}
		} else if (aIsNum) {
			return -1;
		} else if (bIsNum) {
			return 1;
		} else if (ap !== bp) {
			return ap < bp ? -1 : 1;
		}
	}
	return al - bl;
}

module.exports = { getNextVersion, isValidVersion, compareVersions };
